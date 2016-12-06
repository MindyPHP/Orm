<?php

namespace Mindy\Component\Template\Expression;

/**
 * Class SubExpression
 * @package Mindy\Component\Template
 */
class SubExpression extends BinaryExpression
{
    public function operator()
    {
        return '-';
    }
}

