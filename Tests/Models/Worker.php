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
 * Class Worker.
 *
 * @property string name
 * @property \Mindy\Orm\ManyToManyManager projects
 */
class Worker extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class,
            ],
            'projects' => [
                'class' => ManyToManyField::class,
                'modelClass' => Project::class,
                'through' => ProjectMembership::class,
                'link' => ['worker_id', 'project_id'],
            ],
        ];
    }
}
