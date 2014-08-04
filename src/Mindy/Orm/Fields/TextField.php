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
 * @date 03/01/14.01.2014 22:01
 */

namespace Mindy\Orm\Fields;


use Mindy\Form\Fields\TextAreaField;

class TextField extends Field
{
    public function sqlType()
    {
        return 'text';
    }

    public function getFormField($form, $fieldClass = null)
    {
        return parent::getFormField($form, TextAreaField::className());
    }
}
