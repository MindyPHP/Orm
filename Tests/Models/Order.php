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
 * @date 04/03/14.03.2014 01:17
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
