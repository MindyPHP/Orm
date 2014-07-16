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


use Exception;
use Mindy\Orm\Sync;
use Mindy\Query\Connection;
use Mindy\Orm\Model;

class DatabaseTestCase extends TestCase
{
    public $settings = [];

    public function setUp()
    {
        parent::setUp();
        $this->settings = require __DIR__ . '/config_local.php';
        $this->setConnection('mysql');
        $this->initModels($this->getModels());
    }

    public function tearDown()
    {
        $this->dropModels($this->getModels());
        parent::tearDown();
    }

    public function getModels()
    {
        return [];
    }

    public function setConnection($name)
    {
        if(array_key_exists($name, $this->settings)) {
            Model::setConnection(new Connection($this->settings[$name]));
        }
    }

    public function initModels(array $models)
    {
        $sync = new Sync($models);
        $sync->delete();
        $sync->create();
    }

    public function dropModels(array $models)
    {
        $sync = new Sync($models);
        $sync->delete();
    }

    public function getConnectionType()
    {
        $params = explode(':', Model::getConnection()->dsn);
        return array_pop($params);
    }
}
