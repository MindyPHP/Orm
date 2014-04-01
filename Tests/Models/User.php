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

namespace Tests\Models;


use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Model;
use Mindy\Orm\Validator\MaxLengthValidator;
use Mindy\Orm\Validator\MinLengthValidator;

/**
 * Class User
 * @package Tests\Models
 * @property string username
 * @property string password
 */
class User extends Model
{
    public function getFields()
    {
        return [
            'username' => [
                'class' => CharField::className(),
                'validators' => [
                    new MinLengthValidator(3),
                    new MaxLengthValidator(20),
                ]
            ],
            'password' => ['class' => CharField::className()]
        ];
    }
}
