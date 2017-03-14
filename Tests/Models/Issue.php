<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
