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
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\TextField;
use Mindy\Orm\Model;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Product.
 *
 * @property string name
 * @property string price
 * @property string description
 * @property \Mindy\Orm\Tests\Models\Category category
 * @property \Mindy\Orm\Manager lists
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
                    new Assert\Length(['min' => 3]),
                ],
            ],
            'price' => [
                'class' => CharField::class,
                'default' => 0,
            ],
            'description' => [
                'class' => TextField::class,
                'null' => true,
            ],
            'category' => [
                'class' => ForeignField::class,
                'modelClass' => Category::class,
                'null' => true,
            ],
            'lists' => [
                'class' => ManyToManyField::class,
                'modelClass' => ProductList::class,
                'link' => ['product_id', 'product_list_id'],
            ],
        ];
    }
}
