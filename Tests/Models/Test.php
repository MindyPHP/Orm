<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 19:28.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Model;

class Test extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class,
                'verboseName' => 'Name',
            ],
        ];
    }

    public static function tableName()
    {
        return 'tests_test';
    }
}
