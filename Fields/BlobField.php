<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
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
