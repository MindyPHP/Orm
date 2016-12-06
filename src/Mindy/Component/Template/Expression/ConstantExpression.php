<?php

namespace Mindy\Component\Template\Expression;

use Mindy\Component\Template\Compiler;
use Mindy\Component\Template\Expression;

/**
 * Class ConstantExpression
 * @package Mindy\Component\Template
 */
class ConstantExpression extends Expression
{
    protected $value;

    public function __construct($value, $line)
    {
        parent::__construct($line);
        $this->value = $value;
    }

    public function compile(Compiler $compiler, $indent = 0)
    {
        $compiler->repr($this->value);
    }
}

