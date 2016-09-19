<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 19:04
 */

namespace Mindy\Tests\Orm\Models;

use Mindy\Orm\Fields\IntField;
use Mindy\Orm\NewOrm;

class CustomPrimaryKeyModel extends NewOrm
{
    public static function getFields()
    {
        return [
            'id' => [
                'class' => IntField::class,
                'primary' => true
            ],
        ];
    }
}