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

use Mindy\Exception\Exception;
use Mindy\Validation\JsonValidator;

class JsonField extends TextField
{
    public function init()
    {
        $this->validators = array_merge([new JsonValidator], $this->validators);
    }

    public function decode($value)
    {
        if (is_string($value)) {
            /*
             * Try decode json
             */
            $data = json_decode($value);
            if (json_last_error() == JSON_ERROR_NONE) {
                $this->value = $data;
            } else {
                throw new Exception("Cannot decode json value: {$this->value}");
            }
        } else {
            $this->value = $value;
        }
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
        return is_string($this->value) ? $this->value : json_encode($this->value, true);
    }
}
