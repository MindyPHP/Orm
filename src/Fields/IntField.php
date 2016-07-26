<?php

namespace Mindy\Orm\Fields;

/**
 * Class IntField
 * @package Mindy\Orm
 */
class IntField extends Field
{
    public $length = 11;

    public function setValue($value)
    {
        $this->value = (int)$value;
    }

    public function sqlNullable()
    {
        return $this->sqlType() === 'pk' ? '' : parent::sqlNullable();
    }

    public function sqlType()
    {
        return $this->primary ? 'pk' : 'integer(' . (int)$this->length . ')';
    }
}
