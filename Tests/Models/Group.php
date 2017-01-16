<?php
/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 *
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 04/03/14.03.2014 01:15
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
