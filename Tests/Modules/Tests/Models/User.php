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
use Mindy\Orm\Fields\PasswordField;
use Mindy\Orm\Model;
use Mindy\Validation\MaxLengthValidator;
use Mindy\Validation\MinLengthValidator;

/**
 * Class User
 * @package Modules\Tests\Models
 * @property string username
 * @property string password
 */
class User extends Model
{
    public static function getFields()
    {
        return [
            'username' => [
                'class' => CharField::className(),
                'validators' => [
                    new MinLengthValidator(3),
                    new MaxLengthValidator(20),
                ]
            ],
            'password' => [
                'class' => PasswordField::className(),
                'null' => true
            ],
            'groups' => [
                'class' => ManyToManyField::className(),
                'modelClass' => Group::className(),
                'through' => Membership::className()
            ],
            'addresses' => [
                'class' => HasManyField::className(),
                'modelClass' => Customer::className(),
                'relatedName' => 'user',
                'editable' => false,
            ],
        ];
    }
}
