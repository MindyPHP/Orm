<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Model;

/**
 * Class Order.
 *
 * @property \Mindy\Orm\Tests\Models\Customer customer
 * @property \Mindy\Orm\ManyToManyManager products
 */
class Order extends Model
{
    public static function getFields()
    {
        return [
            'customer' => [
                'class' => ForeignField::class,
                'modelClass' => Customer::class,
            ],
            'products' => [
                'class' => ManyToManyField::class,
                'modelClass' => Product::class,
                'link' => ['order_id', 'product_id'],
            ],
            'discount' => [
                'class' => IntField::class,
                'null' => true,
            ],
        ];
    }
}
