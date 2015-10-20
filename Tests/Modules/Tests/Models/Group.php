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
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Model;

/**
 * Class Group
 * @package Modules\Tests\Models
 * @property string name
 * @property \Mindy\Orm\ManyToManyManager users
 */
class Group extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className()
            ],
            'users' => [
                'class' => ManyToManyField::className(),
                'modelClass' => User::className(),
                'through' => Membership::className()
            ]
        ];
    }
}
