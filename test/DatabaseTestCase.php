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

namespace Tests;

use Mindy\Base\Mindy;
use Mindy\Orm\Sync;
use Mindy\Query\Connection;
use Mindy\Query\ConnectionManager;
use Mindy\Tests\DatabaseTestCase;

class OrmDatabaseTestCase extends DatabaseTestCase
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
     * @var ConnectionManager
     */
    protected $manager;
    /**
     * @var Connection
     */
    protected $connection;

    public function setUp()
    {
        if (getenv('TRAVIS')) {
            $this->settings = require __DIR__ . '/config_travis.php';
        } else {
            if (is_file(__DIR__ . '/config_local.php')) {
                $this->settings = require __DIR__ . '/config_local.php';
            } else {
                $this->settings = [
                    'sqlite' => [
                        'class' => '\Mindy\Query\Connection',
                        'dsn' => 'sqlite::memory:',
                    ]
                ];
            }
        }
        $this->manager = Mindy::app()->db;
        $this->initModels($this->getModels(), $this->getConnection());
    }

    protected function assertSql($expected, $actual)
    {
        $this->assertEquals($this->getConnection()->getAdapter()->quoteSql($expected), trim($actual));
    }

    /**
     * @return Connection
     */
    protected function getConnection()
    {
        return Mindy::app()->db->getDb($this->driver);
    }

    protected function getModels()
    {
        return [];
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->app = null;
        $this->dropModels($this->getModels(), $this->getConnection());
    }

    public function initModels(array $models, Connection $connection)
    {
        $sync = new Sync($models, $connection);
        $sync->delete();
        $sync->create();
    }

    public function dropModels(array $models, Connection $connection)
    {
        $sync = new Sync($models, $connection);
        $sync->delete();
    }

    public function getConnectionType()
    {
        $params = explode(':', $this->connection->dsn);
        return array_pop($params);
    }
}
