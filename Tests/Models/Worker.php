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
