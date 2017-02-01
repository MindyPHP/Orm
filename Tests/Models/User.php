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
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\PasswordField;
use Mindy\Orm\Model;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User.
 *
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
                'null' => false,
                'validators' => [
                    new Assert\Length(['min' => 3, 'max' => 20]),
                ],
            ],
            'password' => new PasswordField([
                'null' => true,
            ]),
            'groups' => [
                'class' => ManyToManyField::class,
                'modelClass' => Group::class,
                'through' => Membership::class,
                'link' => ['user_id', 'group_id'],
            ],
            'addresses' => [
                'class' => HasManyField::class,
                'modelClass' => Customer::class,
                'link' => ['user_id', 'id'],
                'editable' => false,
            ],
        ];
    }

    public static function tableName()
    {
        return 'users';
    }

    public static function objectsManager($instance = null)
    {
        $className = get_called_class();

        return new UserManager($instance ? $instance : new $className());
    }
}
