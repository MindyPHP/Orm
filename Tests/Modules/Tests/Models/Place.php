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
use Mindy\Orm\Fields\OneToOneField;
use Mindy\Orm\Model;

/**
 * Class Place
 * @package Modules\Tests\Models
 * @property string name
 * @property \Modules\Tests\Models\Restaurant restaurant
 */
class Place extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className()
            ],
            'restaurant' => [
                'class' => OneToOneField::className(),
                'modelClass' => Restaurant::className(),
                'reversed' => true
            ]
        ];
    }
}
