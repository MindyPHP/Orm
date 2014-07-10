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
 * @date 04/03/14.03.2014 01:14
 */

namespace Tests\Models;


use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\TextField;
use Mindy\Orm\Model;

/**
 * Class Customer
 * @package Tests\Models
 * @property \Tests\Models\User user
 * @property string address
 */
class Customer extends Model
{
    public static function getFields()
    {
        return [
            'user' => [
                'class' => ForeignField::className(),
                'modelClass' => User::className()
            ],
            'address' => ['class' => TextField::className()]
        ];
    }
}
