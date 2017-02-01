<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
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
