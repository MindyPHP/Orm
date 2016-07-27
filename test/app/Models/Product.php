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

use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\TextField;
use Mindy\Orm\Model;
use Mindy\Orm\Validator\MaxLengthValidator;

/**
 * Class Product
 * @package Modules\Tests\Models
 * @property string name
 * @property string price
 * @property string description
 * @property \Modules\Tests\Models\Category category
 * @property \Mindy\Orm\ManyToManyManager lists
 */
class Product extends Model
{
    public $type = 'SIMPLE';

    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class,
                'default' => 'Product',
                'validators' => [
                    function ($value) {
                        if (mb_strlen($value, 'UTF-8') < 3) {
                            return "Minimal length < 3";
                        }

                        return true;
                    },
                ]
            ],
            'price' => ['class' => CharField::class, 'default' => 0],
            'description' => ['class' => TextField::class, 'null' => true],
            'category' => [
                'class' => ForeignField::class,
                'modelClass' => Category::class,
                'null' => true,
                'relatedName' => 'products'
            ],
            'lists' => [
                'class' => ManyToManyField::class,
                'modelClass' => ProductList::class,
                'throughLink' => ['product_id', 'product_list_id']
            ]
        ];
    }
}
