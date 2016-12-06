<?php

namespace Mindy\Component\Template\Node;

use Mindy\Component\Template\Compiler;
use Mindy\Component\Template\Expression\ArrayExpression;
use Mindy\Component\Template\Node;

/**
 * Class IncludeNode
 * @package Mindy\Component\Template
 */
class IncludeNode extends Node
{
    protected $include;
    protected $params;

    public function __construct($include, $params, $line)
    {
        parent::__construct($line);
        $this->include = $include;
        $this->params = $params;
    }

    public function compile(Compiler $compiler, $indent = 0)
    {
        $compiler->addTraceInfo($this, $indent);
        $compiler->raw('$this->loadInclude(', $indent);
        $this->include->compile($compiler);
        $compiler->raw(')->display(');

        if ($this->params instanceof ArrayExpression) {
            $this->params->compile($compiler);
            $compiler->raw(' + ');
        }

        $compiler->raw('$context);' . "\n");
    }
}

