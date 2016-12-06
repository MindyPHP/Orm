<?php

namespace Mindy\Component\Template\Node;

use Mindy\Component\Template\Compiler;
use Mindy\Component\Template\Node;

/**
 * Class ParentNode
 * @package Mindy\Component\Template
 */
class ParentNode extends Node
{
    protected $name;

    public function __construct($name, $line)
    {
        parent::__construct($line);
        $this->name = $name;
    }

    public function compile(Compiler $compiler, $indent = 0)
    {
        $compiler->addTraceInfo($this, $indent);
        $compiler->raw(
            '$this->displayParent(\'' . $this->name .
            '\', $context, $blocks, $macros, $imports);' . "\n", $indent
        );
    }
}

