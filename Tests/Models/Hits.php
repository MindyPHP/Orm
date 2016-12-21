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
 * @date 05/05/14.05.2014 19:53
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Model;

class Hits extends Model
{
    public static function getFields()
    {
        return [
            'hits' => [
                'class' => IntField::class,
                'default' => 0,
            ],
        ];
    }
}
