<?php

namespace Mindy\Orm\Fields;

/**
 * Class TimeField
 * @package Mindy\Orm
 */
class TimeField extends Field
{
    public function sqlType()
    {
        return 'time';
    }

    public function sqlDefault()
    {
        return $this->default === null ? '' : "DEFAULT '{$this->default}'";
    }
}
