<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use Mindy\Orm\Flysystem\UrlPlugin;

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
            $adapter = new Local(realpath(__DIR__.'/Orm/app/media'));
            $this->filesystem = new Filesystem($adapter, new Config([
                'disable_asserts' => true,
            ]));

            $this->filesystem->addPlugin(new UrlPlugin());
        }

        return $this->filesystem;
    }
}

function app()
{
    $mock = new \stdClass();
    $mock->db = new MockDb();
    $mock->storage = new MockStorage();

    return $mock;
}
