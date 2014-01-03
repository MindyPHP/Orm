<?php
/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 03/01/14.01.2014 21:58
 */

namespace Mindy\Db\Validator;


abstract class Validator
{
    private $_errors;

    abstract public function validate($value);

    protected function addError($error)
    {
        $this->_errors[] = $error;
    }

    public function clearErrors()
    {
        $this->_errors = [];
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function hasErrors()
    {
        return !empty($this->_errors);
    }
}
