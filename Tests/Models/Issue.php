<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 19:27.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\DateTimeField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Model;

class Issue extends Model
{
    public static function getFields()
    {
        return [
            'author' => [
                'class' => ForeignField::class,
                'modelClass' => User1::class,
            ],
            'user' => [
                'class' => ForeignField::class,
                'modelClass' => User1::class,
            ],
            'created_at' => [
                'class' => DateTimeField::class,
                'autoNowAdd' => true,
            ],
        ];
    }

    public static function tableName()
    {
        return 'issue';
    }
}
