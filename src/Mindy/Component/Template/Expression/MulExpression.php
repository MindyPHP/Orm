<?php

namespace Mindy\Component\Template\Expression;

/**
 * Class MulExpression
 * @package Mindy\Component\Template
 */
class MulExpression extends BinaryExpression
{
    public function operator()
    {
        return '*';
    }
}
