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
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Model;

class ModelWheel extends Model
{
    const TYPE_STAMP = 1;
    const TYPE_CAST = 2;

    public static function tableName()
    {
        return 'mir_model_wheel';
    }

    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class,
                'verboseName' => 'Наименование',
            ],
            'upper_name' => [
                'class' => CharField::class,
                'verboseName' => 'НАИМЕНОВАНИЕ',
                'null' => true,
            ],
            'producer_wheel_id' => [
                'class' => IntField::class,
                'verboseName' => 'Производитель',
            ],
            'type' => [
                'class' => IntField::class,
                'verboseName' => 'Тип',
                'choices' => [
                    self::TYPE_STAMP => 'Штампованый',
                    self::TYPE_CAST => 'Литой',
                ],
            ],
            'color' => [
                'class' => CharField::class,
                'verboseName' => 'Цвет',
                'null' => true,
            ],
            'image' => [
                'class' => CharField::class,
                'verboseName' => 'Изображение',
                'null' => true,
            ],
        ];
    }
}
