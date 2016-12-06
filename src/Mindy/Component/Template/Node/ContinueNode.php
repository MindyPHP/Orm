<?php

namespace Mindy\Component\Template\Node;

use Mindy\Component\Template\Compiler;
use Mindy\Component\Template\Node;

/**
 * Class ContinueNode
 * @package Mindy\Component\Template
 */
class ContinueNode extends Node
{
    public function compile(Compiler $compiler, $indent = 0)
    {
        $compiler->addTraceInfo($this, $indent);
        $compiler->raw("continue;\n", $indent);
    }
}

