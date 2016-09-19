<?php

namespace Mindy\Orm\Fields;
use Doctrine\DBAL\Types\Type;

/**
 * Class BlobField
 * @package Mindy\Orm
 */
class BlobField extends Field
{
    public function getSqlType()
    {
        return Type::getType(Type::BLOB);
    }
}

