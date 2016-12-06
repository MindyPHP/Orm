<?php

namespace Mindy\Component\Template\Expression;

use Mindy\Component\Template\Compiler;

/**
 * Class OrExpression
 * @package Mindy\Component\Template
 */
class OrExpression extends LogicalExpression
{
    public function compile(Compiler $compiler, $indent = 0)
    {
        $compiler->raw('(($a = ', $indent);
        $this->left->compile($compiler);
        $compiler->raw(') ? ($a) : (');
        $this->right->compile($compiler);
        $compiler->raw('))');
    }
}

