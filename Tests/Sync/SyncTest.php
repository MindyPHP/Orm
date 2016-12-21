<?php
/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 *
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 04/01/14.01.2014 00:53
 */

namespace Mindy\Orm\Tests\Sync;

use Mindy\Orm\Sync;
use Mindy\Orm\Tests\OrmDatabaseTestCase;
use Mindy\Orm\Tests\Models\Category;
use Mindy\Orm\Tests\Models\Product;
use Mindy\Orm\Tests\Models\ProductList;
use Mindy\Orm\Tests\Models\User;

abstract class SyncTest extends OrmDatabaseTestCase
{
    public function testCreate()
    {
        $sync = new Sync([new ProductList()], $this->getConnection());
        $sync->delete();
        $sync->create();

        $schemaManager = $this->getConnection()->getSchemaManager();
        $this->assertTrue($schemaManager->tablesExist(['product_list']));
    }

    protected function getTableNames()
    {
        $tableNames = [];
        foreach ($this->getConnection()->getSchemaManager()->listTables() as $table) {
            $tableNames[] = $table->getName();
        }

        return $tableNames;
    }

    public function testDrop()
    {
        $model = new ProductList();

        $schemaManager = $this->getConnection()->getSchemaManager();
        $sync = new Sync([$model], $this->getConnection());

        $sync->create();
        $this->assertTrue($schemaManager->tablesExist(['product_list']));

        $sync->delete();
        $this->assertFalse($schemaManager->tablesExist(['product_list']));
    }

    public function testSyncBoth()
    {
        $c = $this->getConnection();
        $schemaManager = $this->getConnection()->getSchemaManager();
        foreach ($schemaManager->listTables() as $table) {
            $schemaManager->dropTable($table);
        }

        $this->assertEquals([], $this->getTableNames());

        $sync = new Sync([new ProductList(), new Category(), new User(), new Product()], $c);
        $created = $sync->create();
        $this->assertEquals(5, $created);

        $this->assertEquals(5, count($this->getTableNames()));

        $sync->delete();
        $this->assertEquals([], $this->getTableNames());
    }
}
