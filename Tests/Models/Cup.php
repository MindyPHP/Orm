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
 * @date 04/03/14.03.2014 01:15
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
