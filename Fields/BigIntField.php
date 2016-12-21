<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 20:01.
 */

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Types\Type;

class BigIntField extends IntField
{
    public function getSqlType()
    {
        return Type::getType(Type::BIGINT);
    }
}
