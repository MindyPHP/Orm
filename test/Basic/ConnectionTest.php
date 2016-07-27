<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 27/07/16
 * Time: 13:32
 */

namespace Mindy\Orm\Tests\Basic;

use Mindy\Base\Mindy;
use Mindy\Query\Connection;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testId()
    {
        $db = Mindy::app()->db;

        $connection = $db->getDb('mysql');
        $this->assertInstanceOf(Connection::class, $connection);
        $newConnection = $db->getDb('mysql');
        $this->assertSame($connection->getId(), $newConnection->getId());

        $connection = $db->getDb('sqlite');
        $this->assertInstanceOf(Connection::class, $connection);
        $newConnection = $db->getDb('sqlite');
        $this->assertSame($connection->getId(), $newConnection->getId());

        $connection = $db->getDb('pgsql');
        $this->assertInstanceOf(Connection::class, $connection);
        $newConnection = $db->getDb('pgsql');
        $this->assertSame($connection->getId(), $newConnection->getId());
    }
}