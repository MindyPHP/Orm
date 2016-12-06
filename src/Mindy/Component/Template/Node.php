<?php

namespace Mindy\Component\Template;

/**
 * Class Node
 * @package Mindy\Component\Template
 */
class Node
{
    /**
     * @var int
     */
    protected $line;

    /**
     * Node constructor.
     * @param $line
     */
    public function __construct($line)
    {
        $this->line = $line;
    }

    /**
     * @return mixed
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param \Mindy\Component\Template\Compiler $compiler
     * @param $indent
     */
    public function addTraceInfo(Compiler $compiler, $indent)
    {
        $compiler->addTraceInfo($this, $indent);
    }

    /**
     * @param Compiler $compiler
     * @param int $indent
     */
    public function compile(Compiler $compiler, $indent = 0)
    {
    }
}

