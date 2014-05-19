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
 * @date 03/01/14.01.2014 21:59
 */

namespace Mindy\Orm\Validator;

class JsonValidator extends Validator
{
    public function validate($value)
    {
        if (is_object($value)) {
            $this->addError(["Not json serialize object: " . gettype($value)]);
        }

        return $this->hasErrors() === false;
    }
}
