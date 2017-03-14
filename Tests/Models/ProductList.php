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
use Mindy\Orm\Fields\DateTimeField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Model;

/**
 * Class ProductList.
 *
 * @property string name
 * @property \Mindy\Orm\ManyToManyManager products
 */
class ProductList extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class,
            ],
            'products' => [
                'class' => ManyToManyField::class,
                'modelClass' => Product::class,
                'link' => ['product_list_id', 'product_id'],
            ],
            'date_action' => [
                'class' => DateTimeField::class,
                'required' => false,
                'null' => true,
            ],
        ];
    }
}
