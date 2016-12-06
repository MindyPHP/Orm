<?php

namespace Mindy\Component\Template\Node;

use Mindy\Component\Template\Compiler;
use Mindy\Component\Template\Node;

/**
 * Class ImportNode
 * @package Mindy\Component\Template
 */
class ImportNode extends Node
{
    protected $module;
    protected $import;

    public function __construct($module, $import, $line)
    {
        parent::__construct($line);
        $this->module = $module;
        $this->import = $import;
    }

    public function compile(Compiler $compiler, $indent = 0)
    {
        $compiler->addTraceInfo($this, $indent);
        $compiler->raw("'$this->module' => ", $indent);
        $compiler->raw('$this->loadImport(');
        $this->import->compile($compiler);
        $compiler->raw("),\n");
    }
}

