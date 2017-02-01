<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Mindy\Orm\Orm;
use Mindy\Orm\Sync;
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

    public function getConfig()
    {
        $path = (@getenv('TRAVIS') ? __DIR__.'/config_travis.php' : __DIR__.'/config_local.php');
        $config = include $path;
        if (isset($config[$this->driver])) {
            return $config[$this->driver];
        }

        return false;
    }

    public function setUp()
    {
        if (extension_loaded('pdo_'.$this->driver) === false) {
            $this->markTestSkipped('pdo_'.$this->driver.' ext required');
        }

        $config = $this->getConfig();
        if (false === $config) {
            $this->markTestSkipped(sprintf(
                'Configuration for %s not available. Available configrations %s',
                $this->driver, implode(', ', array_keys($config))
            ));
        }
        $this->connection = DriverManager::getConnection($config);
        Orm::setDefaultConnection($this->connection);

        $this->initModels($this->getModels(), $this->getConnection());
    }

    protected function assertSql($expected, $actual)
    {
        $sql = QueryBuilder::getInstance($this->getConnection())->getAdapter()->quoteSql(str_replace([" \n", "\n"], ' ', $expected));
        $this->assertEquals($sql, trim($actual));
    }

    /**
     * @return Connection
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    protected function getModels()
    {
        return [];
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->dropModels($this->getModels(), $this->getConnection());
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
