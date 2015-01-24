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
 * @date 03/01/14.01.2014 21:58
 */

namespace Mindy\Orm\Fields;

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
