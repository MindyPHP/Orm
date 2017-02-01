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
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Model;

/**
 * Class Group.
 *
 * @property string name
 * @property \Mindy\Orm\ManyToManyManager users
 */
class Group extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class,
            ],
            'users' => [
                'class' => ManyToManyField::class,
                'modelClass' => User::class,
                'through' => Membership::class,
                'link' => ['group_id', 'user_id'],
            ],
        ];
    }

    public static function objectsManager($instance = null)
    {
        $className = get_called_class();

        return new GroupManager($instance ? $instance : new $className());
    }
}
