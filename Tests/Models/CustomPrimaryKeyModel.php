<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 19:04.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\IntField;
use Mindy\Orm\AbstractModel;

class CustomPrimaryKeyModel extends AbstractModel
{
    public static function getFields()
    {
        return [
            'id' => [
                'class' => IntField::class,
                'primary' => true,
            ],
        ];
    }
}
