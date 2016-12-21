<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 *
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 14/02/15 16:31
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
