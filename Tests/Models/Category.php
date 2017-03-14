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
 * Class Category.
 *
 * @property string name
 * @property \Mindy\Orm\HasManyManager products
 */
class Category extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class,
            ],
            'products' => [
                'class' => HasManyField::class,
                'modelClass' => Product::class,
                'null' => true,
                'editable' => false,
                'link' => ['category_id', 'id'],
            ],
        ];
    }
}
