<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
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
