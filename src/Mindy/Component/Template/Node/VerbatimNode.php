<?php

namespace Mindy\Component\Template\Node;

use Mindy\Component\Template\Compiler;
use Mindy\Component\Template\Node;

/**
 * TODO
 * Class VerbatimNode
 * @package Mindy\Component\Template
 */
class VerbatimNode extends OutputNode
{
    public function compile(Compiler $compiler, $indent = 0)
    {
        $compiler->addTraceInfo($this, $indent);
    }
}
