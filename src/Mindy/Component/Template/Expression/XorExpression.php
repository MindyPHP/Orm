<?php

namespace Mindy\Component\Template\Expression;

/**
 * Class XorExpression
 * @package Mindy\Component\Template
 */
class XorExpression extends BinaryExpression
{
    public function operator()
    {
        return 'xor';
    }
}

