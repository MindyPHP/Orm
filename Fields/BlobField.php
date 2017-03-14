<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Types\Type;

/**
 * Class BlobField.
 */
class BlobField extends Field
{
    public function getSqlType()
    {
        return Type::getType(Type::BLOB);
    }
}
