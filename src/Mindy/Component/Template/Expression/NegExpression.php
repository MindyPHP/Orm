<?php

namespace Mindy\Component\Template\Expression;

use Mindy\Component\Template\Compiler;

/**
 * Class NegExpression
 * @package Mindy\Component\Template
 */
class NegExpression extends UnaryExpression
{
    public function operator(Compiler $compiler)
    {
        $compiler->raw('-');
    }
}

