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
 * @date 04/01/14.01.2014 20:41
 */

namespace Tests\Orm;

use Mindy\Query\ConnectionManager;
use Modules\Tests\Models\Category;
use Modules\Tests\Models\Product;
use Tests\OrmDatabaseTestCase;

abstract class HasManyFieldTest extends OrmDatabaseTestCase
{
    public function getModels()
    {
        return [new Product, new Category];
    }

    public function testSimple()
    {
        $categoryToys = new Category([
            'name' => 'Toys'
        ]);
        $this->assertTrue($categoryToys->getIsNewRecord());
        $categoryToys->save();
        $this->assertFalse($categoryToys->getIsNewRecord());

        $category_animals = new Category();
        $category_animals->name = 'Animals';
        $category_animals->save();

        $db = ConnectionManager::getDb();
        $tableSql = $db->schema->quoteColumnName('tests_product');
        $tableAliasSql = $db->schema->quoteColumnName('tests_product_1');
        $categoryIdSql = $db->schema->quoteColumnName('category_id');

        $this->assertEquals("SELECT COUNT(*) FROM $tableSql $tableAliasSql WHERE ($tableAliasSql.$categoryIdSql='1')", $categoryToys->products->countSql());
        $this->assertEquals(0, $categoryToys->products->count());

        $product_bear = new Product([
            'category' => $categoryToys,
            'name' => 'Bear',
            'price' => 100,
            'description' => 'Funny white bear'
        ]);
        $product_bear->save();

        $this->assertEquals(1, $categoryToys->products->count());

        $product_rabbit = new Product([
            'category' => $category_animals,
            'name' => 'Rabbit',
            'price' => 110,
            'description' => 'Rabbit with carrot'
        ]);
        $product_rabbit->save();

        $this->assertEquals(1, $categoryToys->products->count());

        $product_rabbit->category = $categoryToys;
        $product_rabbit->save();

        $this->assertEquals(2, $categoryToys->products->count());
    }

    public function testThrough()
    {

    }
}
