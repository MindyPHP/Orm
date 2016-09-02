<?php

namespace Mindy\Orm\Fields;

/**
 * Class BooleanField
 * @package Mindy\Orm
 */
class BooleanField extends Field
{
    public $default = false;

    public function sqlType()
    {
        return 'boolean';
    }

    public function sqlDefault()
    {
        $adapter = $this->getModel()->getDb()->getAdapter();
        $default = (string)$adapter->getBoolean($this->default);
        $sql = $this->default === null ? '' : "DEFAULT {$default}";
        return $sql;
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

    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\CheckboxField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }
}
