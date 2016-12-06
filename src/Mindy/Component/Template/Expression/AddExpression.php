<?php

namespace Mindy\Component\Template\Expression;

/**
 * Class AddExpression
 * @package Mindy\Component\Template
 */
class AddExpression extends BinaryExpression
{
    public function operator()
    {
        return '+';
    }
}

