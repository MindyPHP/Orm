<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 04/01/14.01.2014 03:23
 */

namespace Mindy\Db\Traits;


trait YiiCompatible
{
    public static function getDb()
    {
        return static::getConnection();
    }
}
