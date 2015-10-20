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

namespace Modules\Tests\Models;


use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Model;

/**
 * Class Order
 * @package Modules\Tests\Models
 * @property \Modules\Tests\Models\Customer customer
 * @property \Mindy\Orm\ManyToManyManager products
 */
class Order extends Model
{
    public static function getFields()
    {
        return [
            'customer' => [
                'class' => ForeignField::className(),
                'modelClass' => Customer::className()
            ],
            'products' => [
                'class' => ManyToManyField::className(),
                'modelClass' => Product::className()
            ],
            'discount' => [
                'class' => IntField::className(),
                'null' => true
            ]
        ];
    }
}
