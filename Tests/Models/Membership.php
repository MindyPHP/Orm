<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Model;

class Membership extends Model
{
    public static function getFields()
    {
        return [
            'group' => [
                'class' => ForeignField::class,
                'modelClass' => Group::class,
            ],
            'user' => [
                'class' => ForeignField::class,
                'modelClass' => User::class,
            ],
        ];
    }
}
