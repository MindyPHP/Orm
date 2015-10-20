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

namespace Tests\Orm;

use Mindy\Orm\Sync;
use Modules\Tests\Models\Category;
use Modules\Tests\Models\Product;
use Modules\Tests\Models\ProductList;
use Modules\Tests\Models\User;
use Tests\OrmDatabaseTestCase;

abstract class SyncTest extends OrmDatabaseTestCase
{
    public function testSync()
    {
        $product = new Product();
        $this->assertEquals(6, count($product->getFieldsInit()));

        $models = [
            new ProductList(),
            new Category(),
            new User(),
            $product,
        ];

        $sync = new Sync($models);
        $sync->delete();

        foreach ($models as $model) {
            $this->assertFalse($sync->hasTable($model));
        }

        // Create all tables. If table exists - skip.
        $sync->create();

        foreach ($models as $model) {
            $this->assertTrue($sync->hasTable($model));
        }

        // Remove all tables. If table does not exists - skip.
        $sync->delete();

        foreach ($models as $model) {
            $this->assertFalse($sync->hasTable($model));
        }
    }
}
