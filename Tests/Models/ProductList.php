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
