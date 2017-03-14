<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\BlobField;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Model;

/**
 * Class Session.
 */
class Session extends Model
{
    public static function getFields()
    {
        return [
            'id' => [
                'class' => CharField::class,
                'length' => 32,
                'primary' => true,
                'null' => false,
            ],
            'expire' => [
                'class' => IntField::class,
                'null' => false,
            ],
            'data' => [
                'class' => BlobField::class,
                'null' => true,
            ],
        ];
    }

    public function __toString()
    {
        return (string) $this->id;
    }
}
