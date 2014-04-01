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


use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\TextField;
use Mindy\Orm\Model;
use Mindy\Orm\Validator\MaxLengthValidator;

/**
 * Class Product
 * @package Tests\Models
 * @property string name
 * @property string price
 * @property string description
 * @property \Tests\Models\Category category
 * @property \Mindy\Orm\ManyToManyManager lists
 */
class Product extends Model
{
    public $type = 'SIMPLE';

    public function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className(),
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
            'price' => ['class' => CharField::className()],
            'description' => ['class' => TextField::className()],
            'category' => [
                'class' => ForeignField::className(),
                'modelClass' => Category::className()
            ],
            'lists' => [
                'class' => ManyToManyField::className(),
                'modelClass' => ProductList::className()
            ]
        ];
    }
}