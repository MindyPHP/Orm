<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/09/16
 * Time: 11:26
 */

namespace Mindy;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class MockDb
{
    public function getConnection($name = null)
    {
        return \Connections::getConnectionManager()->getConnection($name);
    }
}

class MockStorage
{
    protected $filesystem;

    public function getFilesystem()
    {
        if ($this->filesystem === null) {
            $adapter = new Local(realpath(__DIR__ . '/Orm/app/media'));
            $this->filesystem = new Filesystem($adapter);
        }
        return $this->filesystem;
    }
}

function app()
{
    $mock = new \stdClass;
    $mock->db = new MockDb;
    $mock->storage = new MockStorage;
    return $mock;
}