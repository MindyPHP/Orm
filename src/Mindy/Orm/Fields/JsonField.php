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
 * @date 03/01/14.01.2014 22:02
 */

namespace Mindy\Orm\Fields;

use Mindy\Helper\Json;
use Mindy\Validation\JsonValidator;

class JsonField extends TextField
{
    public function init()
    {
        $this->validators = array_merge([new JsonValidator], $this->validators);
    }

    private function decode($value)
    {
        $this->value = is_string($value) ? Json::decode($value) : $value;
        return $this->value;
    }

    public function getValue()
    {
        return $this->decode($this->value);
    }

    public function setDbValue($value)
    {
        return $this->decode($value);
    }

    public function setValue($value)
    {
        return $this->decode($value);
    }

    public function getDbPrepValue()
    {
        return is_string($this->value) ? $this->value : Json::encode($this->value);
    }
}
