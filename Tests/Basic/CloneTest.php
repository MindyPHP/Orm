<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Basic;

use Doctrine\DBAL\DriverManager;
use Mindy\Orm\Manager;
use Mindy\Orm\QuerySet;
use Mindy\Orm\Tests\Models\Dummy;
use PHPUnit\Framework\TestCase;

class CloneTest extends TestCase
{
    protected function getQuerySet()
    {
        $config = [
            'memory' => true,
            'driver' => 'pdo_sqlite',
            'driverClass' => 'Mindy\QueryBuilder\Database\Sqlite\Driver',
        ];
        $connection = DriverManager::getConnection($config);

        $model = new Dummy();
        $model->setConnection($connection);

        $qs = new QuerySet();
        $qs->setModel($model);
        $qs->setConnection($connection);

        return $qs;
    }

    protected function getManager()
    {
        $config = [
            'memory' => true,
            'driver' => 'pdo_sqlite',
            'driverClass' => 'Mindy\QueryBuilder\Database\Sqlite\Driver',
        ];
        $connection = DriverManager::getConnection($config);

        $model = new Dummy();
        $model->setConnection($connection);

        return new Manager($model, $connection);
    }

    public function testCloneQuerySet()
    {
        $qs = $this->getQuerySet();

        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1', $qs->allSql());

        $qs->filter(['id' => 1]);
        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1 WHERE (dummy_1.id = 1)', $qs->allSql());

        $cloneQs = clone $qs;
        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1 WHERE (dummy_1.id = 1)', $cloneQs->allSql());
    }

    public function testCloneQuerySetBefore()
    {
        $qs = $this->getQuerySet();
        $cloneQs = clone $qs;

        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1', $qs->allSql());
        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1', $cloneQs->allSql());

        $qs->filter(['id' => 1]);
        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1 WHERE (dummy_1.id = 1)', $qs->allSql());
        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1', $cloneQs->allSql());
    }

    public function testCloneManager()
    {
        $manager = $this->getManager();

        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1', $manager->allSql());

        $manager->filter(['id' => 1]);
        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1 WHERE (dummy_1.id = 1)', $manager->allSql());

        $cloneManager = clone $manager;
        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1 WHERE (dummy_1.id = 1)', $cloneManager->allSql());
    }

    public function testCloneManagerBefore()
    {
        $manager = $this->getManager();
        $cloneManager = clone $manager;

        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1', $manager->allSql());
        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1', $cloneManager->allSql());

        $manager->filter(['id' => 1]);
        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1 WHERE (dummy_1.id = 1)', $manager->allSql());
        $this->assertSame('SELECT dummy_1.* FROM dummy AS dummy_1', $cloneManager->allSql());
    }
}
