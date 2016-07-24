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
                    'default' => [
                        'class' => '\Mindy\Query\Connection',
                        'dsn' => 'sqlite::memory:',
                    ]
                ];
            }
        }
        $this->manager = new ConnectionManager(['databases' => $this->settings]);
        $this->connection = $this->manager->getDb($this->driver);
        $this->initModels($this->getModels());
    }

    protected function getModels()
    {
        return [];
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->dropModels($this->getModels());
    }

    public function initModels(array $models)
    {
        $sync = new Sync($models, $this->connection);
        $sync->delete();
        $sync->create();
    }

    public function dropModels(array $models)
    {
        $sync = new Sync($models, $this->connection);
        $sync->delete();
    }

    public function getConnectionType()
    {
        $params = explode(':', $this->connection->dsn);
        return array_pop($params);
    }
}
