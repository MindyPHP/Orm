<?php

namespace Mindy\Component\Template;

/**
 * Class Library
 * @package Mindy\Component\Template
 */
abstract class Library
{
    /**
     * @var \Mindy\Component\Template\Parser
     */
    protected $parser;
    /**
     * @var \Mindy\Component\Template\TokenStream
     */
    protected $stream;

    /**
     * @return array
     */
    abstract public function getHelpers();

    /**
     * @return array
     */
    abstract public function getTags();

    public function setParser(Parser $parser)
    {
        $this->parser = $parser;
        return $this;
    }

    public function setStream(TokenStream $stream)
    {
        $this->stream = $stream;
        return $this;
    }
}
