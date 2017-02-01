<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Model;

class Membership extends Model
{
    public static function getFields()
    {
        return [
            'group' => [
                'class' => ForeignField::class,
                'modelClass' => Group::class,
            ],
            'user' => [
                'class' => ForeignField::class,
                'modelClass' => User::class,
            ],
        ];
    }
}
