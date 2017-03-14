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
use Mindy\Orm\Model;

class TestChanged extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class,
                'length' => 50,
                'verboseName' => 'Name',
            ],
        ];
    }

    public static function tableName()
    {
        return 'tests_test_changed';
    }
}
