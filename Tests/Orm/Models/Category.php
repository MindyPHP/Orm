<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 04/03/14.03.2014 01:17
 */

namespace Mindy\Tests\Orm\Models;


use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Model;

/**
 * Class Category
 * @package Mindy\Tests\Orm\Models
 * @property string name
 * @property \Mindy\Orm\HasManyManager products
 */
class Category extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class
            ],
            'products' => [
                'class' => HasManyField::class,
                'modelClass' => Product::class,
                'null' => true,
                'editable' => false,
                'link' => ['category_id', 'id']
            ],
        ];
    }
}
