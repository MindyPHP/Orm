<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 06/12/2016
 * Time: 21:30
 */

namespace Mindy\Orm;

use Doctrine\DBAL\Driver\Connection;

class Orm
{
    protected static $connection;

    public static function setDefaultConnection(Connection $connection)
    {
        self::$connection = $connection;
    }

    public static function getDefaultConnection()
    {
        return self::$connection;
    }
}