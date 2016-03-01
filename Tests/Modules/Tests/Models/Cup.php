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
 * @date 04/03/14.03.2014 01:15
 */

namespace Modules\Tests\Models;


use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Model;

/**
 * Class Cup
 * @package Modules\Tests\Models
 * @property string name
 * @property \Mindy\Orm\HasManyManager designs
 * @property \Mindy\Orm\HasManyManager colors
 */
class Cup extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className()
            ],
            'designs' => [
                'class' => HasManyField::className(),
                'modelClass' => Design::className()
            ],
            'colors' => [
                'class' => HasManyField::className(),
                'modelClass' => Color::className()
            ]
        ];
    }
}
