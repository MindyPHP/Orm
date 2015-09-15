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

use Mindy\Query\ConnectionManager;
use Mindy\Tests\DatabaseTestCase;

class OrmDatabaseTestCase extends DatabaseTestCase
{
    public $settings = [];

    public $driver = 'sqlite';

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
        ConnectionManager::$defaultDatabase = $this->driver;
        parent::setUp();
    }

    public function getConnectionType()
    {
        $params = explode(':', ConnectionManager::getDb()->dsn);
        return array_pop($params);
    }
}
