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

namespace Tests\Models;


use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Model;

/**
 * Class Order
 * @package Tests\Models
 * @property \Tests\Models\Customer customer
 * @property \Tests\Models\Product[] products
 */
class Order extends Model
{
    public function getFields()
    {
        return [
            'customer' => [
                'class' => ForeignField::className(),
                'modelClass' => Customer::className()
            ],
            'products' => [
                'class' => ManyToManyField::className(),
                'modelClass' => Product::className()
            ]
        ];
    }
}
