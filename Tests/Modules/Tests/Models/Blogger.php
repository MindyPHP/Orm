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
 * @date 04/03/14.03.2014 01:17
 */

namespace Modules\Tests\Models;


use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\TextField;
use Mindy\Orm\Model;

/**
 * Class Blogger
 * @package Modules\Tests\Models
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
                'class' => CharField::className()
            ],
            /**
             * This subscribers subscribed to blogger
             */
            'subscribers' => [
                'class' => ManyToManyField::className(),
                'modelClass' => self::className(),
            ],
            /**
             * Blogger has these subscriptions
             */
            'subscribes' => [
                'class' => ManyToManyField::className(),
                'modelClass' => self::className(),
                'reversed' => true
            ],
        ];
    }
}
