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
    public function setUp()
    {
        parent::setUp();

        $connection = new Connection([
            'dsn' => 'mysql:host=localhost;dbname=tmp',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
        ]);
        Model::setConnection($connection);
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
}
