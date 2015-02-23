<?php

namespace Mindy\Orm\Fields;

use Mindy\Form\Fields\CheckboxField;

/**
 * Class BooleanField
 * @package Mindy\Orm\Fields
 */
class BooleanField extends Field
{
    public $default = false;

    public function sql()
    {
        return trim(sprintf('%s %s %s', $this->sqlType(), $this->sqlNullable(), $this->sqlDefault()));
    }

    public function sqlType()
    {
        return 'bool';
    }

    public function setValue($value)
    {
        $this->value = (bool)$value;
    }

    public function getValue()
    {
        return (bool)$this->value;
    }

    public function getDbPrepValue()
    {
        return (bool)$this->value;
    }

    public function getFormField($form, $fieldClass = null, array $extra = [])
    {
        return parent::getFormField($form, CheckboxField::className(), $extra);
    }
}
