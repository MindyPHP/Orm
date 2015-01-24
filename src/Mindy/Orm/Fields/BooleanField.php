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
 * @date 04/01/14.01.2014 02:43
 */

namespace Mindy\Orm\Fields;


use Mindy\Form\Fields\CheckboxField;

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
