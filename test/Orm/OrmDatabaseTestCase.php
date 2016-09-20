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
 * @date 03/01/14.01.2014 23:20
 */

namespace Mindy\Tests\Orm;

use Connections;
use Doctrine\DBAL\Connection;
use Mindy\MockApp;
use Mindy\Orm\Sync;
use Mindy\QueryBuilder\ConnectionManager;
use Mindy\QueryBuilder\QueryBuilder;

class OrmDatabaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    public $settings = [];
    /**
     * @var string
     */
    public $driver = 'sqlite';
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    public function setUp()
    {
        if (extension_loaded('pdo_' . $this->driver) === false) {
            $this->markTestSkipped('pdo_' . $this->driver . ' ext required');
        }

        $this->connectionManager = Connections::getConnectionManager()->setDefaultConnection($this->driver);

        $this->initModels($this->getModels(), $this->getConnection());
    }

    protected function assertSql($expected, $actual)
    {
        $sql = QueryBuilder::getInstance($this->getConnection())->getAdapter()->quoteSql(str_replace([" \n", "\n"], " ", $expected));
        $this->assertEquals($sql, trim($actual));
    }

    /**
     * @return Connection
     */
    protected function getConnection()
    {
        return $this->connectionManager->getConnection($this->driver);
    }

    protected function getModels()
    {
        return [];
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->dropModels($this->getModels(), $this->getConnection());
        Mindy::setApplication(null);
        $this->app = null;
    }

    public function initModels(array $models, Connection $connection)
    {
        foreach ($connection->getSchemaManager()->listTables() as $table) {
            $connection->getSchemaManager()->dropTable($table);
        }

        $sync = new Sync($models, $connection);
        $sync->create();
    }

    public function dropModels(array $models, Connection $connection)
    {
        foreach ($connection->getSchemaManager()->listTables() as $table) {
            $connection->getSchemaManager()->dropTable($table);
        }

//        $sync = new Sync($models, $connection);
//        $sync->delete();
    }

    public function getConnectionType()
    {
        $params = explode(':', $this->connection->dsn);
        return array_pop($params);
    }

    protected function mockModel($className)
    {
        $instance = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods(['setConnection'])->getMock();
        $instance->method('getConnection')->willReturn($this->connection);
        return $instance;
    }
}
