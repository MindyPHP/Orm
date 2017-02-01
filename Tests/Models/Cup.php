<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
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
