<?php

namespace Mindy\Component\Template;

use Closure;
use Exception;
use Mindy\Component\Template\Helper;
use RuntimeException;

/**
 * Class Template
 * @package Mindy\Component\Template
 */
abstract class Template
{
    /**
     * @var string
     */
    public $helperClassName = '\Mindy\Component\Template\Helper';
    /**
     * @var array
     */
    public $internalHelpers = [
        'is_array',
        'is_object',
        'is_string',
        'number_format',
        'nl2br',
        'substr_count',
        'dirname',
        'basename',
        'time',
        'strtotime'
    ];
    /**
     * @var Loader
     */
    protected $loader;
    /**
     * @var array
     */
    protected $helpers;
    /**
     * @var null
     */
    protected $parent;
    /**
     * @var array
     */
    protected $blocks;
    /**
     * @var array
     */
    protected $macros;
    /**
     * @var array
     */
    protected $imports;
    /**
     * @var array
     */
    protected $stack;
    /**
     * @var array|VariableProviderInterface[]
     */
    protected $variableProviders = array();

    /**
     * Template constructor.
     * @param Loader $loader
     * @param array $helpers
     * @param array|VariableProviderInterface[] $variablesProviders
     */
    public function __construct(Loader $loader, $helpers = array(), $variablesProviders = array())
    {
        $this->loader = $loader;
        $this->helpers = $helpers;
        $this->variableProviders = $variablesProviders;
        $this->parent = null;
        $this->blocks = array();
        $this->macros = array();
        $this->imports = array();
        $this->stack = array();
    }

    public function loadExtends($template)
    {
        if ($template == static::NAME) {
            throw new Exception("Template cannot be inherited from himself: " . static::NAME);
        }
        try {
            return $this->loader->load($template, static::NAME);
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('error extending %s (%s) from %s line %d',
                var_export($template, true), $e->getMessage(), static::NAME,
                $this->getLineTrace($e)
            ));
        }
    }

    public function loadInclude($template)
    {
        try {
            return $this->loader->load($template, static::NAME);
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('error including %s (%s) from %s line %d',
                var_export($template, true), $e->getMessage(), static::NAME,
                $this->getLineTrace($e)
            ));
        }
    }

    public function loadImport($template)
    {
        try {
            return $this->loader->load($template, static::NAME)->getMacros();
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('error importing %s (%s) from %s line %d',
                var_export($template, true), $e->getMessage(), static::NAME,
                $this->getLineTrace($e)
            ));
        }
    }

    public function displayBlock($name, $context, $blocks, $macros, $imports)
    {
        $blocks = $blocks + $this->blocks;
        $macros = $macros + $this->macros;
        $imports = $imports + $this->imports;
        if (isset($blocks[$name]) && is_callable($blocks[$name])) {
            return call_user_func($blocks[$name], $context, $blocks, $macros, $imports);
        }
    }

    public function displayParent($name, $context, $blocks, $macros, $imports)
    {
        $parent = $this;
        while ($parent = $parent->parent) {
            if (isset($parent->blocks[$name]) && is_callable($parent->blocks[$name])) {
                return call_user_func($parent->blocks[$name], $context, $blocks, $macros, $imports);
            }
        }
    }

    public function expandMacro($module, $name, $params, $context, $macros, $imports)
    {
        $macros = $macros + $this->macros;
        $imports = $imports + $this->imports;
        if (isset($module) && isset($imports[$module])) {
            $macros = $macros + $imports[$module];
        }
        if (isset($macros[$name]) && is_callable($macros[$name])) {
            return call_user_func($macros[$name], $params, $context, $macros, $imports);
        }
    }

    public function pushContext(&$context, $name)
    {
        if (!array_key_exists($name, $this->stack)) {
            $this->stack[$name] = array();
        }
        array_push($this->stack[$name], isset($context[$name]) ? $context[$name] : null);
        return $this;
    }

    public function popContext(&$context, $name)
    {
        if (!empty($this->stack[$name])) {
            $context[$name] = array_pop($this->stack[$name]);
        }
        return $this;
    }

    public function getLineTrace(Exception $e = null)
    {
        if (!isset($e)) {
            $e = new Exception;
        }

        $lines = static::$lines;

        $file = get_class($this) . '.php';

        foreach ($e->getTrace() as $trace) {
            if (isset($trace['file']) && basename($trace['file']) == $file) {
                $line = $trace['line'];
                return isset($lines[$line]) ? $lines[$line] : null;
            }
        }
        return null;
    }

    /**
     * @param $name
     * @param array $args
     * @return mixed
     * @throws \RuntimeException
     */
    public function helper($name, $args = array())
    {
        $args = func_get_args();
        $name = array_shift($args);

        if (isset($this->helpers[$name]) && is_callable($this->helpers[$name])) {
            return call_user_func_array($this->helpers[$name], $args);
        } else if (($helper = array($this->helperClassName, $name)) && is_callable($helper)) {
            return call_user_func_array($helper, $args);
        } else if (function_exists($name) && in_array($name, $this->internalHelpers)) {
            if (isset($args[0])) {
                $args[0] = (string)$args[0];
            }
            return call_user_func_array($name, $args);
        }

        throw new RuntimeException(sprintf('undefined helper "%s" in %s line %d', $name, static::NAME, $this->getLineTrace()));
    }

    /**
     * @param array $context
     * @param array $blocks
     * @param array $macros
     * @param array $imports
     * @return string
     */
    abstract public function display($context = array(), $blocks = array(), $macros = array(), $imports = array());

    /**
     * @param array $context
     * @param array $blocks
     * @param array $macros
     * @param array $imports
     * @return string
     */
    public function render($context = array(), $blocks = array(), $macros = array(), $imports = array())
    {
        ob_start();
        $this->display($this->mergeContext($context), $blocks, $macros, $imports);
        return ob_get_clean();
    }

    /**
     * @param array $context
     * @return array
     */
    protected function mergeContext($context = array())
    {
        foreach ($this->variableProviders as $variableProvider) {
            $context = array_merge($context, $variableProvider->getData());
        }
        return $context;
    }

    public function iterate($context, $seq)
    {
        if (isset($context['loop'])) {
            $iter = $context['loop'];
        } elseif (isset($context['forloop'])) {
            $iter = $context['forloop'];
        } else {
            $iter = null;
        }
        return new Helper\ContextIterator($seq, $iter);
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function getMacros()
    {
        return $this->macros;
    }

    public function getImports()
    {
        return $this->imports;
    }

    public function getAttr($obj, $attr, $args = array())
    {
        if (is_array($obj)) {
            if (isset($obj[$attr])) {
                if ($obj[$attr] instanceof Closure) {
                    if (is_array($args)) {
                        array_unshift($args, $obj);
                    } else {
                        $args = array($obj);
                    }
                    return call_user_func_array($obj[$attr], $args);
                } else {
                    return $obj[$attr];
                }
            } else {
                return null;
            }
        } elseif (is_object($obj)) {
            if (is_array($args)) {
                $callable = array($obj, $attr);
                return is_callable($callable) ? call_user_func_array($callable, $args) : null;
            } else {
                $members = array_keys(get_object_vars($obj));
                $methods = get_class_methods(get_class($obj));
                if (in_array($attr, $members)) {
                    return @$obj->$attr;
                } elseif (in_array('__call', $methods) && method_exists($obj, $attr)) {
                    return call_user_func_array([$obj, $attr], is_array($args) ? $args : []);
                } elseif (in_array('__get', $methods)) {
                    return $obj->__get($attr);
                } else {
                    $callable = array($obj, $attr);
                    return is_callable($callable) ? call_user_func($callable) : null;
                }
            }
        } else {
            return null;
        }
    }

    public function setAttr(&$obj, $attrs, $value)
    {
        if (empty($attrs)) {
            $obj = $value;
            return;
        }
        $attr = array_shift($attrs);
        if (is_object($obj)) {
            $class = get_class($obj);
            $members = array_keys(get_object_vars($obj));
            if (!in_array($attr, $members)) {
                if (empty($attrs) && method_exists($obj, '__set')) {
                    $obj->__set($attr, $value);
                    return;
                } elseif (property_exists($class, $attr)) {
                    throw new RuntimeException("inaccessible '$attr' object attribute");
                } else {
                    if ($attr === null || $attr === false || $attr === '') {
                        if ($attr === null) {
                            $token = 'null';
                        }
                        if ($attr === false) {
                            $token = 'false';
                        }
                        if ($attr === '') {
                            $token = 'empty string';
                        }
                        throw new RuntimeException(sprintf('invalid object attribute (%s) in %s line %d', $token, static::NAME, $this->getLineTrace()));
                    }
                    $obj->{$attr} = null;
                }
            }
            if (!isset($obj->$attr)) {
                $obj->$attr = null;
            }
            $this->setAttr($obj->$attr, $attrs, $value);
        } else {
            if (!is_array($obj)) {
                $obj = array();
            }
            $this->setAttr($obj[$attr], $attrs, $value);
        }
    }
}

