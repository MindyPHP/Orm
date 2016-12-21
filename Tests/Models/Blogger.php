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
 * @date 04/03/14.03.2014 01:17
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
