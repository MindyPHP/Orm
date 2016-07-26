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
 * @date 04/01/14.01.2014 00:53
 */

namespace Mindy\Orm\Tests;

use Mindy\Orm\Sync;
use Modules\Tests\Models\Category;
use Modules\Tests\Models\Product;
use Modules\Tests\Models\ProductList;
use Modules\Tests\Models\User;
use Tests\OrmDatabaseTestCase;

abstract class SyncTest extends OrmDatabaseTestCase
{
    public function testCreate()
    {
        $sync = new Sync([new ProductList], $this->connection);
        $sync->delete();
        $sync->create();
        $tables = $this->connection->getSchema()->getTableNames('', true);
        $this->assertTrue(in_array('product_list', $tables));
    }

    public function testDrop()
    {
        $sync = new Sync([new ProductList], $this->connection);
        $sync->create();
        $tables = $this->connection->getSchema()->getTableNames('', true);
        $this->assertTrue(in_array('product_list', $tables));
        $sync->delete();
        $tables = $this->connection->getSchema()->getTableNames('', true);
        $this->assertFalse(in_array('product_list', $tables));
    }

    public function testSyncBoth()
    {
        $c = $this->connection;
        $this->assertEquals([], $c->getSchema()->getTableNames('', true));

        $sync = new Sync([
            new ProductList,
            new Category,
            new User,
            new Product,
        ], $c);
        $sync->create();

        $tables = $c->getSchema()->getTableNames('', true);
        $this->assertTrue(in_array('category', $tables));
        $this->assertTrue(in_array('product', $tables));
        $this->assertTrue(in_array('product_list', $tables));
        $this->assertTrue(in_array('product_product_list', $tables));
        $this->assertTrue(in_array('user', $tables));

        $sync->delete();
        $this->assertEquals([], $c->getSchema()->getTableNames('', true));
    }
}
