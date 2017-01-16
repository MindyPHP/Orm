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
 * @date 15/07/14.07.2014 19:14
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Model;

class Permission extends Model
{
    public static function getFields()
    {
        return [
            'code' => [
                'class' => CharField::class,
            ],
            'groups' => [
                'class' => ManyToManyField::class,
                'modelClass' => Group::class,
            ],
        ];
    }
}
