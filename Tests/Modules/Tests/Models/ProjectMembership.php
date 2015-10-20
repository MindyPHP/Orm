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
 * @date 04/01/14.01.2014 21:19
 */

namespace Modules\Tests\Models;


use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Model;

class ProjectMembership extends Model
{
    public static function getFields()
    {
        return [
            'project' => [
                'class' => ForeignField::className(),
                'modelClass' => Project::className()
            ],
            'worker' => [
                'class' => ForeignField::className(),
                'modelClass' => Worker::className()
            ],
            'position' => [
                'class' => IntField::className()
            ],
            'curator' => [
                'class' => ForeignField::className(),
                'modelClass' => Worker::className()
            ]
        ];
    }
}
