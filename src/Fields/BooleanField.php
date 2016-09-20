<?php

namespace Mindy\Orm\Fields;
use Doctrine\DBAL\Types\Type;
use Mindy\QueryBuilder\QueryBuilder;

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

    public function setValue($value)
    {
        $this->value = (bool)$value;
    }

    public function getValue()
    {
        return (bool)$this->value;
    }

    public function getDbPrepValue()
    {
        return (bool)$this->value;
    }

    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\CheckboxField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
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
}
