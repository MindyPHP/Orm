<?php

namespace Mindy\Component\Template;

use InvalidArgumentException;
use Mindy\Component\Template\Adapter\FileAdapter;
use RuntimeException;

/**
 * Class Loader
 * @package Mindy\Component\Template
 */
class Loader
{
    const CLASS_PREFIX = '__MindyTemplate_';

    const RECOMPILE_NEVER = -1;
    const RECOMPILE_NORMAL = 0;
    const RECOMPILE_ALWAYS = 1;
    /**
     * @var bool enable exception handler
     */
    public $exceptionHandler = true;

    /**
     * @var array
     */
    protected $options = array();
    /**
     * @var array|VariableProviderInterface[]
     */
    protected $variableProviders = array();
    /**
     * @var array
     */
    protected $paths = array();
    /**
     * @var array
     */
    protected $cache = array();
    /**
     * @var array
     */
    protected $libraries = array();

    /**
     * Loader constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['source'])) {
            throw new RuntimeException('missing source directory');
        }

        if (!isset($options['target'])) {
            throw new RuntimeException('missing target directory');
        }

        $target = $options['target'];
        if ($target instanceof \Closure) {
            $target = $target->__invoke();
        }

        $source = $options['source'];
        if ($source instanceof \Closure) {
            $source = $source->__invoke();
        }
        $options += array(
            'mode' => self::RECOMPILE_NORMAL,
            'mkdir' => 0777,
            'helpers' => array(),
            'globals' => array(),
            'autoEscape' => true
        );

        if (!isset($options['adapter'])) {
            $options['adapter'] = new FileAdapter($source);
        }

        if (!is_dir($target)) {
            if ($options['mkdir'] === false) {
                throw new RuntimeException(sprintf('target directory %s not found', $target));
            }
            if (!mkdir($target, $options['mkdir'], true)) {
                throw new RuntimeException(sprintf('unable to create target directory %s', $target));
            }
        }

        $this->options = array(
            'source' => is_array($source) ? $source : [$source],
            'target' => $target,
            'mode' => $options['mode'],
            'adapter' => $options['adapter'],
            'helpers' => $options['helpers'],
            'globals' => $options['globals'],
            'autoEscape' => $options['autoEscape'],
        );

        $this->paths = array();
        $this->cache = array();

        $this->addLibrary(new DefaultLibrary);
    }

    /**
     * @param VariableProviderInterface $variableProvider
     */
    public function addVariableProvider(VariableProviderInterface $variableProvider)
    {
        $this->variableProviders[] = $variableProvider;
    }

    /**
     * @param $exception
     */
    protected function handleSyntaxError($exception)
    {
        if ($this->exceptionHandler) {
            $adapter = $this->getAdapter();
            echo $this->renderString(file_get_contents(__DIR__ . '/templates/debug.html'), [
                'exception' => $exception,
                'source' => $adapter->getContents($exception->getTemplateFile()),
                'styles' => file_get_contents(__DIR__ . '/templates/core.css') . file_get_contents(__DIR__ . '/templates/exception.css'),
                'loader' => $this
            ]);
            die();
        } else {
            throw $exception;
        }
    }

    public function normalizePath($path)
    {
        $path = preg_replace('#/{2,}#', '/', strtr($path, '\\', '/'));
        $parts = array();
        foreach (explode('/', $path) as $i => $part) {
            if ($part === '..') {
                if (!empty($parts)) array_pop($parts);
            } elseif ($part !== '.') {
                $parts[] = $part;
            }
        }
        return $parts;
    }

    /**
     * @param $template
     * @param string $from
     * @return string
     */
    public function resolvePath($template, $from = '')
    {
        foreach ($this->options['source'] as $sourcePath) {
            $source = implode('/', $this->normalizePath($sourcePath));
            $file = $source . '/' . ltrim($template, '/');
            if (is_file($file)) {
                $parts = $this->normalizePath($source . '/' . dirname($from) . '/' . $template);
                foreach ($this->normalizePath($source) as $i => $part) {
                    if ($part !== $parts[$i]) {
                        throw new RuntimeException(sprintf('%s is outside the source directory', $template));
                    }
                }
                return $template;
            }
        }

        throw new RuntimeException(sprintf('Template %s not found', $template));
    }

    /**
     * @return Adapter\Adapter
     */
    protected function getAdapter()
    {
        return $this->options['adapter'];
    }

    public function compile($template, $mode = null)
    {
        if (!is_string($template)) {
            throw new \InvalidArgumentException('string expected');
        }

        $adapter = $this->getAdapter();

        $path = $this->resolvePath($template);

        $class = self::CLASS_PREFIX . md5($path);

        if (!$adapter->isReadable($path)) {
            throw new RuntimeException(sprintf('%s is not a valid readable template', $template));
        }

        $target = $this->options['target'] . '/' . $class . '.php';

        if (!isset($mode)) {
            $mode = $this->options['mode'];
        }

        switch ($mode) {
            case self::RECOMPILE_ALWAYS:
                $compile = true;
                break;
            case self::RECOMPILE_NEVER:
                $compile = !file_exists($target);
                break;
            case self::RECOMPILE_NORMAL:
            default:
                $compile = !file_exists($target) || filemtime($target) < $adapter->lastModified($path);
                break;
        }

        if ($compile) {
            try {
                $lexer = new Lexer($adapter->getContents($path));
                $parser = new Parser($lexer->tokenize());
                $parser->setAutoEscape($this->options['autoEscape']);
                $parser->setLibraries($this->libraries);
                $compiler = new Compiler($parser->parse());
                $compiler->compile($path, $target);
            } catch (SyntaxError $e) {
                $e->setTemplateFile($path);
                $this->handleSyntaxError($e->setMessage($path . ': ' . $e->getMessage()));
            }
        }

        return $this;
    }

    /**
     * @param $template
     * @param string $from
     * @return Template
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function load($template, $from = '')
    {
        if ($template instanceof Template) {
            return $template;
        }

        if (!is_string($template)) {
            throw new InvalidArgumentException('string expected');
        }

        $adapter = $this->getAdapter();

        if (isset($this->paths[$template . $from])) {
            $path = $this->paths[$template . $from];
        } else {
            $path = $this->resolvePath($template, $from);
            $this->paths[$template . $from] = $path;
        }

        $class = self::CLASS_PREFIX . md5($path);

        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }


        if (!class_exists($class, false)) {
            if (!$adapter->isReadable($path)) {
                throw new RuntimeException(sprintf('%s is not a valid readable template', $template));
            }

            $target = $this->options['target'] . '/' . $class . '.php';

            switch ($this->options['mode']) {
                case self::RECOMPILE_ALWAYS:
                    $compile = true;
                    break;
                case self::RECOMPILE_NEVER:
                    $compile = !file_exists($target);
                    break;
                case self::RECOMPILE_NORMAL:
                default:
                    $compile = !file_exists($target) || filemtime($target) < $adapter->lastModified($path);
                    break;
            }

            if ($compile) {
                try {
                    $lexer = new Lexer($adapter->getContents($path));
                    $parser = new Parser($lexer->tokenize());
                    $parser->setAutoEscape($this->options['autoEscape']);
                    $parser->setLibraries($this->libraries);
                    $compiler = new Compiler($parser->parse());
                    $compiler->compile($path, $target);
                } catch (SyntaxError $e) {
                    $e->setTemplateFile($path);
                    $this->handleSyntaxError($e->setMessage($path . ': ' . $e->getMessage()));
                }
            }
            require_once $target;
        }

        return $this->cache[$class] = new $class($this, $this->options['helpers'], $this->variableProviders);
    }

    /**
     * @param $template
     * @return Template
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function loadFromString($template)
    {
        if (!is_string($template)) {
            throw new \InvalidArgumentException('string expected');
        }

        $class = self::CLASS_PREFIX . md5($template);

        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }

        $target = $this->options['target'] . '/' . $class . '.php';
        $path = "";

        try {
            $lexer = new Lexer($template);
            $parser = new Parser($lexer->tokenize());
            $parser->setAutoEscape($this->options['autoEscape']);
            $parser->setLibraries($this->libraries);
            $compiler = new Compiler($parser->parse());
            $compiler->compile($template, $target);
        } catch (SyntaxError $e) {
            $e->setTemplateFile($path);
            $this->handleSyntaxError($e->setMessage($path . ': ' . $e->getMessage()));
        }
        require_once $target;

        return $this->cache[$class] = new $class($this, $this->options['helpers'], $this->variableProviders);
    }

    public function isValid($template, &$error = null)
    {
        if (!is_string($template)) {
            throw new InvalidArgumentException('string expected');
        }

        $adapter = $this->getAdapter();
        $path = $this->resolvePath($template);

        if (!$adapter->isReadable($path)) {
            throw new RuntimeException(sprintf(
                '%s is not a valid readable template',
                $template
            ));
        }

        try {
            $lexer = new Lexer($adapter->getContents($path));
            $parser = new Parser($lexer->tokenize());
            $parser->setAutoEscape($this->options['autoEscape']);
            new Compiler($parser->parse());
        } catch (\Exception $e) {
            $error = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * @param $template
     * @param array $data
     * @return string
     */
    public function render($template, array $data = [])
    {
        return $this->load($template)->render($data);
    }

    /**
     * @param $source
     * @param array $data
     * @return string
     */
    public function renderString($source, array $data = [])
    {
        return $this->loadFromString($source)->render($data);
    }

    /**
     * @param $name
     * @param $func
     * @return $this
     */
    public function addHelper($name, $func)
    {
        $this->options['helpers'][$name] = $func;
        return $this;
    }

    /**
     * @param Library $library
     * @return $this
     */
    public function addLibrary(Library $library)
    {
        $this->libraries[] = $library;
        foreach ($library->getHelpers() as $name => $func) {
            $this->addHelper($name, $func);
        }
        return $this;
    }

    public function getVersion()
    {
        return 1.0;
    }
}
