<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Model;

class ProjectMembership extends Model
{
    public static function getFields()
    {
        return [
            'project' => [
                'class' => ForeignField::class,
                'modelClass' => Project::class,
            ],
            'worker' => [
                'class' => ForeignField::class,
                'modelClass' => Worker::class,
            ],
            'position' => [
                'class' => IntField::class,
            ],
            'curator' => [
                'class' => ForeignField::class,
                'modelClass' => Worker::class,
            ],
        ];
    }
}
