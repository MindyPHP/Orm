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

class MaxLengthValidator extends Validator
{
    public $maxLength;

    public function __construct($maxLength)
    {
        $this->maxLength = $maxLength;
    }

    public function validate($value)
    {
        if (mb_strlen($value, 'UTF-8') > $this->maxLength) {
            $this->addError(Translate::getInstance()->t('validation', "Maximum length is {length}", [
                '{length}' => $this->maxLength
            ]));
        }

        return $this->hasErrors() === false;
    }
}
