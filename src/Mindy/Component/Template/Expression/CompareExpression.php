<?php

namespace Mindy\Component\Template\Expression;

use Mindy\Component\Template\Compiler;
use Mindy\Component\Template\Expression;

/**
 * Class CompareExpression
 * @package Mindy\Component\Template
 */
class CompareExpression extends Expression
{
    protected $expr;
    protected $ops;

    public function __construct($expr, $ops, $line)
    {
        parent::__construct($line);
        $this->expr = $expr;
        $this->ops = $ops;
    }

    public function compile(Compiler $compiler, $indent = 0)
    {
        $this->expr->compile($compiler);
        $i = 0;
        foreach ($this->ops as $op) {
            if ($i) {
                $compiler->raw(' && ($tmp' . $i);
            }
            list($op, $node) = $op;
            $compiler->raw(' ' . ($op == '=' ? '==' : $op) . ' ');
            $compiler->raw('($tmp' . ++$i . ' = ');
            $node->compile($compiler);
            $compiler->raw(')');
        }
        if ($i > 1) {
            $compiler->raw(str_repeat(')', $i - 1));
        }
    }
}

