<?php

namespace Mindy\Component\Template\Expression;

use Mindy\Component\Template\Compiler;

/**
 * Class InclusionExpression
 * @package Mindy\Component\Template
 */
class InclusionExpression extends LogicalExpression
{
    public function compile(Compiler $compiler, $indent = 0)
    {
        // if (is_array($haystack))

        $compiler->raw('(is_array(', $indent);
        $this->right->compile($compiler);
        $compiler->raw(') ? ');

        // {

        $compiler->raw('(in_array(', $indent);
        $this->left->compile($compiler);
        $compiler->raw(', (array)');
        $this->right->compile($compiler);
        $compiler->raw('))');

        // } else

        $compiler->raw(' : ', $indent);

        // {

        $compiler->raw('(mb_strstr(', $indent);
        $this->right->compile($compiler);
        $compiler->raw(', ');
        $this->left->compile($compiler);
        $compiler->raw(') != false)');

        // }

        $compiler->raw(')');
    }
}

