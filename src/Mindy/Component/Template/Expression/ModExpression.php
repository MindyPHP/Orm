<?php

namespace Mindy\Component\Template\Expression;

use Mindy\Component\Template\Compiler;

/**
 * Class ModExpression
 * @package Mindy\Component\Template
 */
class ModExpression extends BinaryExpression
{
    public function compile(Compiler $compiler, $indent = 0)
    {
        $compiler->raw('fmod(', $indent);
        $this->left->compile($compiler);
        $compiler->raw(', ');
        $this->right->compile($compiler);
        $compiler->raw(')');
    }
}

