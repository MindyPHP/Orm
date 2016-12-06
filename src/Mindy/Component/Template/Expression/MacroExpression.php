<?php

namespace Mindy\Component\Template\Expression;

use Mindy\Component\Template\Compiler;
use Mindy\Component\Template\Expression;

/**
 * Class MacroExpression
 * @package Mindy\Component\Template
 */
class MacroExpression extends Expression
{
    protected $module;
    protected $name;
    protected $args;

    public function __construct($module, $name, $args, $line)
    {
        parent::__construct($line);
        $this->module = $module;
        $this->name = $name;
        $this->args = $args;
    }

    public function compile(Compiler $compiler, $indent = 0)
    {
        $compiler->raw(
            '$this->expandMacro(\'' . $this->module . '\', \'' . $this->name .
            '\', array(', $indent
        );
        foreach ($this->args as $key => $val) {
            $compiler->raw("'$key' => ");
            $val->compile($compiler);
            $compiler->raw(',');
        }
        if (isset($this->module)) {
            $compiler->raw(
                '), $context, $macros, $imports)'
            );
        } else {
            $compiler->raw('), $context, $macros, $imports)');
        }
    }
}

