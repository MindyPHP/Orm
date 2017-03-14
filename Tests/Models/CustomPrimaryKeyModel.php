<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\AbstractModel;
use Mindy\Orm\Fields\IntField;

class CustomPrimaryKeyModel extends AbstractModel
{
    public static function getFields()
    {
        return [
            'id' => [
                'class' => IntField::class,
                'primary' => true,
            ],
        ];
    }
}
