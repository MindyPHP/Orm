<?php

namespace Mindy\Component\Template;

use Exception;

/**
 * Class SyntaxError
 * @package Mindy\Component\Template
 */
class SyntaxError extends Exception
{
    protected $token;
    protected $path;

    /**
     * SyntaxError constructor.
     * @param string $message
     * @param Token $token
     */
    public function __construct($message, Token $token)
    {
        $this->token = $token;
        $line = $token->getLine();
        $char = $token->getChar();
        parent::__construct("$message in line $line char $char");
    }

    public function setTemplateFile($path)
    {
        $this->path = $path;
        return $this;
    }

    public function getTemplateFile()
    {
        return $this->path;
    }

    public function __toString()
    {
        return (string)$this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }
}

