<?php

namespace Mindy\Orm\Fields;

/**
 * Class DateField
 * @package Mindy\Orm
 */
class DateField extends Field
{
    public $autoFetch = true;

    public function sqlType()
    {
        return 'date';
    }

    public function sqlDefault()
    {
        return $this->default === null ? '' : "DEFAULT '{$this->default}'";
    }

    public function getFormField($form, $fieldClass = null, array $extra = [])
    {
        return parent::getFormField($form, \Mindy\Form\Fields\DateField::className(), $extra);
    }
}
