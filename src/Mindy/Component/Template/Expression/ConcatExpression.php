<?php

namespace Mindy\Component\Template\Expression;

/**
 * Class ConcatExpression
 * @package Mindy\Component\Template
 */
class ConcatExpression extends BinaryExpression
{
    public function operator()
    {
        return '.';
    }
}

