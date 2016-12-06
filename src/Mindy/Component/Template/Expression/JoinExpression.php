<?php

namespace Mindy\Component\Template\Expression;

/**
 * Class JoinExpression
 * @package Mindy\Component\Template
 */
class JoinExpression extends BinaryExpression
{
    public function operator()
    {
        return ".' '.";
    }
}

