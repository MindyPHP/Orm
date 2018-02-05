<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

use Doctrine\DBAL\Connection;
use Mindy\Application\App;

class Orm
{
    /**
     * @var Connection
     */
    protected static $connection;

    /**
     * @param Connection $connection
     */
    public static function setDefaultConnection(Connection $connection)
    {
        self::$connection = $connection;
    }

    /**
     * @return Connection|null
     */
    public static function getDefaultConnection()
    {
        if (null === self::$connection) {
            self::$connection = App::getInstance()
                ->getComponent('orm.connection_manager')
                ->getConnection('default');
        }

        return self::$connection;
    }
}
