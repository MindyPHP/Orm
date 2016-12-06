<?php

namespace Mindy\Component\Template\Expression;

use Mindy\Component\Template\Compiler;
use Mindy\Component\Template\Expression;

/**
 * Class NameExpression
 * @package Mindy\Component\Template
 */
class NameExpression extends Expression
{
    protected $name;

    public function __construct($name, $line)
    {
        parent::__construct($line);
        $this->name = $name;
    }

    public function raw(Compiler $compiler, $indent = 0)
    {
        $compiler->raw($this->name, $indent);
    }

    public function repr(Compiler $compiler, $indent = 0)
    {
        $compiler->repr($this->name, $indent);
    }

    public function compile(Compiler $compiler, $indent = 0)
    {
        $compiler->raw('(array_key_exists(\'' . $this->name . '\', $context) ? ');
        $compiler->raw('$context[\'' . $this->name . '\'] : null)');
    }
}

