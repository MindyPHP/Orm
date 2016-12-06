<?php

namespace Mindy\Component\Template\Expression;

use Mindy\Component\Template\Compiler;

/**
 * Class PosExpression
 * @package Mindy\Component\Template
 */
class PosExpression extends UnaryExpression
{
    public function operator(Compiler $compiler)
    {
        $compiler->raw('+');
    }
}

