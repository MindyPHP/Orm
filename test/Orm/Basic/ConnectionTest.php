<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 27/07/16
 * Time: 13:32
 */

namespace Mindy\Tests\Orm\Basic;

use Doctrine\DBAL\Connection;
use Mindy\Base\Mindy;
use Mindy\Tests\Orm\OrmDatabaseTestCase;

class ConnectionTest extends OrmDatabaseTestCase
{
    public function testId()
    {
        $db = Mindy::app()->db;
        $this->assertInstanceOf(Connection::class, $db->getConnection('mysql'));
        $this->assertInstanceOf(Connection::class, $db->getConnection('sqlite'));
        $this->assertInstanceOf(Connection::class, $db->getConnection('pgsql'));
    }
}