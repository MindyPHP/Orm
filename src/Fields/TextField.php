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
     * @param string $fieldClass
     * @return false|null|string
     */
    public function getFormField($fieldClass = 'Mindy\Form\Fields\TextareaField')
    {
        return parent::getFormField($fieldClass);
    }
}