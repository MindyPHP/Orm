<?php

namespace Mindy\Orm\Fields;

use Mindy\Helper\Json;
use Mindy\Validation\JsonValidator;

/**
 * Class JsonField
 * @package Mindy\Orm
 */
class JsonField extends TextField
{
    public function init()
    {
        $this->validators = array_merge([new JsonValidator], $this->validators);
    }

    private function decode($value)
    {
        if (empty($value)) {
            $value = '{}';
        }
        $this->value = is_string($value) ? Json::decode($value) : $value;
        return $this->value;
    }

    public function getValue()
    {
        return $this->decode($this->value);
    }

    public function setDbValue($value)
    {
        $this->value = $this->decode($value);
        return $this;
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
