<?php

namespace Mindy\Orm\Fields;

/**
 * Class PasswordField
 * @package Mindy\Orm
 */
class PasswordField extends CharField
{
    public function getFormField($form, $fieldClass = null, array $extra = [])
    {
        return parent::getFormField($form, \Mindy\Form\Fields\PasswordField::className(), $extra);
    }
}