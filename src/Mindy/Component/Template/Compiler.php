<?php

namespace Mindy\Component\Template;

use RuntimeException;

/**
 * Class Compiler
 * @package Mindy\Component\Template
 */
class Compiler
{
    protected $fp;
    protected $node;
    protected $line;
    protected $trace;

    /**
     * Compiler constructor.
     * @param $node
     */
    public function __construct($node)
    {
        $this->node  = $node;
        $this->line  = 1;
        $this->trace = array();
    }

    /**
     * @param $raw
     * @param int $indent
     * @return $this
     */
    public function raw($raw, $indent = 0)
    {
        $this->line = $this->line + substr_count($raw, "\n");
        if (!fwrite($this->fp, str_repeat(' ', 4 * $indent) . $raw)) {
            throw new RuntimeException('failed writing to file: ' .  $this->target);
        }

        return $this;
    }

    /**
     * @param $repr
     * @param int $indent
     */
    public function repr($repr, $indent = 0)
    {
        $this->raw(var_export($repr, true), $indent);
    }

    /**
     * @param $name
     * @param $target
     * @param int $indent
     */
    public function compile($name, $target, $indent = 0)
    {
        if (!($this->fp = fopen($target, 'wb'))) {
            throw new RuntimeException('unable to create target file: ' . $target);
        }
        $this->node->compile($name, $this, $indent);
        fclose($this->fp);
    }

    /**
     * @param $name
     * @param int $indent
     * @return $this
     */
    public function pushContext($name, $indent = 0)
    {
        $this->raw('$this->pushContext($context, ', $indent);
        $this->repr($name);
        $this->raw(");\n");
        return $this;
    }

    /**
     * @param $name
     * @param int $indent
     * @return $this
     */
    public function popContext($name, $indent = 0)
    {
        $this->raw('$this->popContext($context, ', $indent);
        $this->repr($name);
        $this->raw(");\n");
        return $this;
    }

    /**
     * @param Node $node
     * @param int $indent
     * @param bool $line
     */
    public function addTraceInfo($node, $indent = 0, $line = true)
    {
        $this->raw(
            '/* line ' . $node->getLine() . " -> " . ($this->line + 1) .
            " */\n", $indent
        );
        if ($line) {
            $this->trace[$this->line] = $node->getLine();
        }
    }

    /**
     * @param bool $export
     * @return array|mixed
     */
    public function getTraceInfo($export = false)
    {
        if ($export) {
            return str_replace(["\n", ' '], '', var_export($this->trace, true));
        }
        return $this->trace;
    }

    /**
     * Free resources
     */
    public function __destruct()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }
}

