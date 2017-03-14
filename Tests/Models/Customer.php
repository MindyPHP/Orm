<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\TextField;
use Mindy\Orm\Model;

/**
 * Class Customer.
 *
 * @property \Mindy\Orm\Tests\Models\User user
 * @property string address
 */
class Customer extends Model
{
    public static function getFields()
    {
        return [
            'user' => [
                'class' => ForeignField::class,
                'modelClass' => User::class,
                'null' => true,
            ],
            'address' => TextField::class,
        ];
    }
}
