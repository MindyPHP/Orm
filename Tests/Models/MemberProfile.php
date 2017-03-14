<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\OneToOneField;
use Mindy\Orm\Model;

class MemberProfile extends Model
{
    public static function getFields()
    {
        return [
            'user' => [
                'class' => OneToOneField::class,
                'modelClass' => Member::class,
                'primary' => true,
                'to' => 'id',
            ],
        ];
    }
}
