<?php

namespace Mindy\Component\Template\Node;

use Mindy\Component\Template\Compiler;
use Mindy\Component\Template\Node;

/**
 * Class SpacelessNode
 * @package Mindy\Component\Template
 */
class SpacelessNode extends OutputNode
{
    public function compile(Compiler $compiler, $indent = 0)
    {
        $compiler->addTraceInfo($this, $indent);
        $compiler->raw('ob_start();ob_implicit_flush(false);', $indent);
        $this->expr->compile($compiler);
        $compiler->raw(";\n");
        $compiler->raw("echo trim(preg_replace('/>\\s+</', '><', ob_get_clean()));\n", $indent);
    }
}

