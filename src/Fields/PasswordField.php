<?php

namespace Mindy\Orm\Fields;

/**
 * Class PasswordField
 * @package Mindy\Orm
 */
class PasswordField extends CharField
{
    /**
     * @param $form
     * @param string $fieldClass
     * @param array $extra
     * @return mixed
     */
    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\PasswordField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }
}