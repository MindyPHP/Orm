<?php

namespace Mindy\Orm\Fields;

/**
 * Class CharField
 * @package Mindy\Orm
 */
class CharField extends Field
{
    public $length = 255;

    public function sqlType()
    {
        return 'string(' . (int)$this->length . ')';
    }

    public function setDbValue($value)
    {
        $this->value = (string)$value;
        return $this;
    }

    public function getDbPrepValue()
    {
        if ($this->value === null && !$this->null && $this->default) {
            return $this->default;
        }
        return $this->value;
    }

    public function sqlDefault()
    {
        return $this->default === null ? '' : "DEFAULT '{$this->default}'";
    }
}
