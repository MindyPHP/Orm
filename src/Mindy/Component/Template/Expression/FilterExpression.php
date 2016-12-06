<?php

namespace Mindy\Component\Template\Expression;

use Mindy\Component\Template\Compiler;
use Mindy\Component\Template\Expression;

/**
 * Class FilterExpression
 * @package Mindy\Component\Template
 */
class FilterExpression extends Expression
{
    protected $node;
    protected $filters;
    protected $autoEscape;

    public function __construct($node, $filters, $autoEscape, $line)
    {
        parent::__construct($line);
        $this->node = $node;
        $this->filters = $filters;
        $this->autoEscape = $autoEscape;
    }

    public function isRaw()
    {
        return in_array('raw', $this->filters) || in_array('safe', $this->filters);
    }

    public function setAutoEscape($autoEscape = true)
    {
        $this->autoEscape = $autoEscape;
        return $this;
    }

    public function appendFilter($filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function prependFilter($filter)
    {
        array_unshift($this->filters, $filter);
        return $this;
    }

    public function compile(Compiler $compiler, $indent = 0)
    {
        static $rawNames = array('raw', 'safe');

        $safe = false;
        $postponed = array();

        foreach ($this->filters as $i => $filter) {
            if (in_array($filter[0], $rawNames)) {
                $safe = true;
                break;
            }
        }

        if ($this->autoEscape && !$safe) {
            $this->appendFilter(array('escape', array()));
        }

        for ($i = count($this->filters) - 1; $i >= 0; --$i) {
            if ($this->filters[$i] === 'raw') {
                continue;
            }
            list($name, $arguments) = $this->filters[$i];
            if (in_array($name, $rawNames)) {
                continue;
            }
            $compiler->raw('$this->helper(\'' . $name . '\', ');
            $postponed[] = $arguments;
        }

        $this->node->compile($compiler);

        foreach (array_reverse($postponed) as $arguments) {
            foreach ($arguments as $arg) {
                $compiler->raw(', ');
                $arg->compile($compiler);
            }
            $compiler->raw(')');
        }
    }
}


