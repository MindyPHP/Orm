<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Types\Type;

/**
 * Class FloatField.
 */
class FloatField extends Field
{
    /**
     * @return string
     */
    public function getSqlType()
    {
        return Type::getType(Type::FLOAT);
    }

    public function getValue()
    {
        return floatval(parent::getValue());
    }
}
