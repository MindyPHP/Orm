<?php

namespace Mindy\Component\Template\Expression;

use Mindy\Component\Template\Compiler;

/**
 * Class NotExpression
 * @package Mindy\Component\Template
 */
class NotExpression extends UnaryExpression
{
    public function operator(Compiler $compiler)
    {
        $compiler->raw('!');
    }
}

