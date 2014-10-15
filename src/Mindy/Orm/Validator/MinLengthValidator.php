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

use Mindy\Locale\Translate;

class MinLengthValidator extends Validator
{
    public $minLength;

    public function __construct($minLength)
    {
        $this->minLength = $minLength;
    }

    public function validate($value)
    {
        $t = Translate::getInstance();

        if(!is_string($value)) {
            $this->addError($t->t('validation', "{type} is not a string", ['{type}' => gettype($value)]));
        }

        if (mb_strlen($value, 'UTF-8') < $this->minLength) {
            $this->addError($t->t('validation', "Minimal length is {length}", ['{length}' => $this->minLength]));
        }

        return $this->hasErrors() === false;
    }
}
