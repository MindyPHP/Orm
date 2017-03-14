<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests;

use Doctrine\DBAL\DriverManager;
use Mindy\Orm\Manager;
use Mindy\Orm\ManagerInterface;
use Mindy\Orm\ModelInterface;
use Mindy\Orm\QuerySet;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testManager()
    {
        $model = $this
            ->getMockBuilder(ModelInterface::class)
            ->getMock();

        $qs = $this
            ->getMockBuilder(QuerySet::class)
            ->getMock();
        $qs->method('all')->willReturn([]);
        $qs->method('count')->willReturn(0);
        $qs->method('get')->willReturn($model);
        $qs->method('min')->willReturn(1);
        $qs->method('max')->willReturn(1);
        $qs->method('average')->willReturn(1);
        $qs->method('sum')->willReturn(1);

        $connection = DriverManager::getConnection([
            'memory' => 'true',
            'driver' => 'pdo_sqlite',
        ]);
//        Orm::setDefaultConnection($connection);

        $manager = new Manager($model, $connection);
        $manager->setQuerySet($qs);
        $manager->setModel($model);
        $this->assertInstanceOf(ModelInterface::class, $manager->getModel());

        $this->assertEquals([], $manager->all());
        $this->assertEquals(0, $manager->count());
        $this->assertEquals(1, $manager->sum('id'));
        $this->assertEquals(1, $manager->average('id'));
        $this->assertEquals(1, $manager->max('id'));
        $this->assertEquals(1, $manager->min('id'));
        $this->assertInstanceOf(ManagerInterface::class, $manager);
        $this->assertInstanceOf(ManagerInterface::class, $manager->group('id'));
        $this->assertInstanceOf(ManagerInterface::class, $manager->asArray());
        $this->assertInstanceOf(ManagerInterface::class, $manager->with('foo'));
        $this->assertInstanceOf(ModelInterface::class, $manager->get(['foo' => 'bar']));
    }
}
