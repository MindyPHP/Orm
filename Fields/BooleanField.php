<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class BooleanField
 * @package Mindy\Orm
 */
class BooleanField extends Field
{
    /**
     * @var bool
     */
    public $default = false;

    /**
     * @return array
     */
    public function getValidationConstraints() : array
    {
        return $this->validators;
    }

    /**
     * @param string $fieldClass
     * @return false|null|string
     */
    public function getFormField($fieldClass = '\Mindy\Form\Fields\CheckboxField')
    {
        return parent::getFormField($fieldClass);
    }

    /**
     * @return string
     */
    public function getSqlType()
    {
        return Type::getType(Type::BOOLEAN);
    }

    /**
     * @return array
     */
    public function getSqlOptions() : array
    {
        $options = parent::getSqlOptions();
        $options['default'] = $this->default;
        return $options;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        parent::setValue((bool)$value);
    }
}
