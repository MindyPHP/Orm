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
 * @date 30/04/14.04.2014 16:36
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Manager;
use Mindy\Orm\Model;

class InstanceTestModel extends Model
{
    public static function objectsManager($instance = null)
    {
        if ($instance) {
            return 123;
        }
        $className = get_called_class();

        return new Manager($instance ? $instance : new $className());
    }
}
