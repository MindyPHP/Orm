<?php

namespace Mindy\Orm\Fields;

/**
 * Class PasswordField
 * @package Mindy\Orm
 */
class PasswordField extends CharField
{
    /**
     * @param string $fieldClass
     * @return false|null|string
     */
    public function getFormField($fieldClass = '\Mindy\Form\Fields\PasswordField')
    {
        return parent::getFormField($fieldClass);
    }
}