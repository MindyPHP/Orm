<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Model;

class Tyre extends Model
{
    public static function getFields()
    {
        return [
            'model_tyre' => [
                'class' => HasManyField::class,
                'modelClass' => ModelTyre::class,
            ],
        ];
    }
}
