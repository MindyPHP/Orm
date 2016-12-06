<?php

namespace Mindy\Component\Template\Node;

use Mindy\Component\Template\Compiler;
use Mindy\Component\Template\Node;

/**
 * Class TextNode
 * @package Mindy\Component\Template
 */
class TextNode extends Node
{
    protected $data;

    public function __construct($data, $line)
    {
        parent::__construct($line);
        $this->data = $data;
    }

    public function compile(Compiler $compiler, $indent = 0)
    {
        if (!strlen($this->data)) {
            return;
        }
        $compiler->addTraceInfo($this, $indent);
        $compiler->raw('echo ', $indent);
        $compiler->repr($this->data);
        $compiler->raw(";\n");
    }
}

