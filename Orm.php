<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm;

use Doctrine\DBAL\Driver\Connection;
use Mindy\Application\App;

class Orm
{
    protected static $connection;

    public static function setDefaultConnection(Connection $connection)
    {
        self::$connection = $connection;
    }

    public static function getDefaultConnection()
    {
        if (self::$connection === null) {
            self::$connection = App::getInstance()
                ->getComponent('orm.connection_manager')
                ->getConnection('default');
        }

        return self::$connection;
    }
}
