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

class NewModel extends DummyModel
{
    public static function getFields()
    {
        return [
            'username' => [
                'class' => CharField::class,
            ],
            'password' => [
                'class' => CharField::class,
            ],
        ];
    }
}
