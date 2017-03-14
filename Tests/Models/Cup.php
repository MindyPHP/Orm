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
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Model;

/**
 * Class Cup.
 *
 * @property string name
 */
class Cup extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class,
            ],
            'designs' => [
                'class' => HasManyField::class,
                'modelClass' => Design::class,
                'link' => ['cup_id', 'id'],
            ],
            'colors' => [
                'class' => HasManyField::class,
                'modelClass' => Color::class,
                'link' => ['cup_id', 'id'],
            ],
        ];
    }
}
