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

class TextField extends Field
{
    public function sqlType()
    {
        return 'text';
    }

    public function getFormField($form, $fieldClass = 'Mindy\Form\Fields\TextAreaField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }
}
