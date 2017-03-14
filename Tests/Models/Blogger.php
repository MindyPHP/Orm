<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Model;

/**
 * Class Blogger.
 *
 * @property string name
 * @property \Mindy\Orm\ManyToManyManager subscribers
 * @property \Mindy\Orm\ManyToManyManager subscribes
 */
class Blogger extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class,
            ],
            /*
             * This subscribers subscribed to blogger
             */
            'subscribers' => [
                'class' => ManyToManyField::class,
                'modelClass' => self::class,
                'link' => ['blogger_to_id', 'blogger_from_id'],
            ],
            /*
             * Blogger has these subscriptions
             */
            'subscribes' => [
                'class' => ManyToManyField::class,
                'modelClass' => self::class,
                'link' => ['blogger_from_id', 'blogger_to_id'],
            ],
        ];
    }
}
