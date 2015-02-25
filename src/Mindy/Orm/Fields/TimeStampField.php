<?php

namespace Mindy\Orm\Fields;

class TimeStampField extends IntField
{
    public function getValue()
    {
        return is_numeric($this->value) ? $this->value : strtotime($this->value);
    }
}