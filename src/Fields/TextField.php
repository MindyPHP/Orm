<?php

namespace Mindy\Orm\Fields;
use Doctrine\DBAL\Types\Type;

/**
 * Class TextField
 * @package Mindy\Orm
 */
class TextField extends Field
{
    /**
     * @return string
     */
    public function getSqlType()
    {
        return Type::getType(Type::TEXT);
    }

    /**
     * @param $form
     * @param string $fieldClass
     * @param array $extra
     * @return mixed
     */
    public function getFormField($form, $fieldClass = 'Mindy\Form\Fields\TextareaField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }
}
