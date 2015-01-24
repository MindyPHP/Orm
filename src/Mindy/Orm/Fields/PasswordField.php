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
 * @date 21/09/14.09.2014 15:52
 */

namespace Mindy\Orm\Fields;


class PasswordField extends CharField
{
    public function getFormField($form, $fieldClass = null, array $extra = [])
    {
        return parent::getFormField($form, \Mindy\Form\Fields\PasswordField::className(), $extra);
    }
}