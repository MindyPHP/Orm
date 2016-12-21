<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Types\Type;

/**
 * Class TextField.
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
}
