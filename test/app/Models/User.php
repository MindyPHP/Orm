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
                'class' => CharField::class,
                'validators' => [
                    new MinLengthValidator(3),
                    new MaxLengthValidator(20),
                ]
            ],
            'password' => [
                'class' => PasswordField::class,
                'null' => true
            ],
            'groups' => [
                'class' => ManyToManyField::class,
                'modelClass' => Group::class,
                'through' => Membership::class,
                'throughLink' => ['user_id', 'group_id']
            ],
            'addresses' => [
                'class' => HasManyField::class,
                'modelClass' => Customer::class,
                'editable' => false,
            ],
        ];
    }

    public static function objectsManager($instance = null)
    {
        $className = get_called_class();
        return new UserManager($instance ? $instance : new $className);
    }
}
