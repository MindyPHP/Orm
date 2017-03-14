<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Model;

class Permission extends Model
{
    public static function getFields()
    {
        return [
            'code' => [
                'class' => CharField::class,
            ],
            'groups' => [
                'class' => ManyToManyField::class,
                'modelClass' => Group::class,
            ],
        ];
    }
}
