<?php

namespace Mindy\Orm\Fields;

/**
 * Class TextField
 * @package Mindy\Orm
 */
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
