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
 * Class Project.
 *
 * @property string name
 * @property \Mindy\Orm\ManyToManyManager workers
 */
class Project extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class,
            ],
            'workers' => [
                'class' => ManyToManyField::class,
                'modelClass' => Worker::class,
                'through' => ProjectMembership::class,
                'link' => ['project_id', 'worker_id'],
            ],
        ];
    }
}
