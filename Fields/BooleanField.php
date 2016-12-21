<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Types\Type;

/**
 * Class BooleanField.
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
    public function getValidationConstraints()
    {
        return $this->validators;
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
    public function getSqlOptions()
    {
        return array_merge(parent::getSqlOptions(), [
            'default' => $this->default,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        parent::setValue((bool) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return (bool) parent::getValue();
    }
}
