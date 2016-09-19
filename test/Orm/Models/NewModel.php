<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 19:43
 */

namespace Mindy\Tests\Orm\Models;

use Mindy\Orm\Fields\CharField;

class NewModel extends DummyModel
{
    public static function getFields()
    {
        return [
            'username' => [
                'class' => CharField::class
            ],
            'password' => [
                'class' => CharField::class
            ],
        ];
    }
}