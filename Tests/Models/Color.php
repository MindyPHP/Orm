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
 * @date 04/03/14.03.2014 01:15
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Model;

/**
 * Class Color.
 *
 * @property string name
 */
class Color extends Model
{
    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::class,
            ],
            'cup' => [
                'class' => ForeignField::class,
                'modelClass' => Cup::class,
            ],
        ];
    }
}
