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


use Tests\DatabaseTestCase;
use Tests\Models\Category;
use Tests\Models\Product;
use Tests\Models\ProductList;

class ManyToManyFieldTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new Category, new ProductList, new Product]);
    }

    public function tearDown()
    {
        $this->dropModels([new Category, new ProductList, new Product]);
    }

    public function testAllSql()
    {
        $qs = Product::objects()->filter(['id' => 1]);
        $this->assertEquals('SELECT * FROM `product` WHERE (`id`=1)', $qs->getSql());
        $this->assertEquals('SELECT * FROM `product` WHERE (`id`=1)', $qs->allSql());
        $this->assertEquals('SELECT COUNT(*) FROM `product` WHERE (`id`=1)', $qs->countSql());
    }

    public function testSimple()
    {
        $category = new Category();
        $category->name = 'Toys';
        $category->save();

        $product = new Product();
        $product->name = 'Bear';
        $product->price = 100;
        $product->description = 'Funny white bear';
        $product->category = $category;

        $this->assertNull($product->pk);
        $this->assertEquals(0, $product->lists->count());
        $this->assertEquals([], $product->lists->all());

        $this->assertTrue($product->save());
        $this->assertEquals(1, $product->pk);

        $list = new ProductList();
        $list->name = 'qwe';
        $this->assertTrue($list->save());

        $list->products->link($product);
        $this->assertEquals(1, count($product->lists->all()));

        $new = Product::objects()->get(['id' => 1]);
        $this->assertEquals(1, count($new->lists->all()));
    }

    public function testExact()
    {
        $qs = Product::objects()->filter(['id' => 2]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(0, $qs->count());
        $this->assertEquals('SELECT COUNT(*) FROM `product` WHERE (`id`=2)', $qs->countSql());
    }

    public function testIsNull()
    {
        $qs = Product::objects()->filter(['id' => null]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(0, $qs->count());
        $this->assertEquals('SELECT COUNT(*) FROM `product` WHERE (`id` IS NULL)', $qs->countSql());
    }

    public function testIn()
    {
        $qs = Product::objects()->filter(['category__in' => [1, 2, 3, 4, 5]]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals('SELECT COUNT(*) FROM `product` WHERE (`category` IN (1, 2, 3, 4, 5))', $qs->countSql());
    }

    public function testGte()
    {
        $qs = Product::objects()->filter(['id__gte' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals('SELECT COUNT(*) FROM `product` WHERE ((`id` >= 1))', $qs->countSql());
    }

    public function testGt()
    {
        $qs = Product::objects()->filter(['id__gt' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals('SELECT COUNT(*) FROM `product` WHERE ((`id` > 1))', $qs->countSql());
    }

    public function testLte()
    {
        $qs = Product::objects()->filter(['id__lte' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals('SELECT COUNT(*) FROM `product` WHERE ((`id` <= 1))', $qs->countSql());
    }

    public function testLt()
    {
        $qs = Product::objects()->filter(['id__lt' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals('SELECT COUNT(*) FROM `product` WHERE ((`id` < 1))', $qs->countSql());
    }

    public function testContains()
    {
        $qs = Product::objects()->filter(['id__contains' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `product` WHERE (`id` LIKE '%1%')", $qs->countSql());
    }

    public function testStartswith()
    {
        $qs = Product::objects()->filter(['id__startswith' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `product` WHERE (`id` LIKE '1%')", $qs->countSql());
    }

    public function testEndswith()
    {
        $qs = Product::objects()->filter(['id__endswith' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `product` WHERE (`id` LIKE '%1')", $qs->countSql());
    }

    public function testRange()
    {
        $qs = Product::objects()->filter(['id__range' => [0, 1]]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals('SELECT COUNT(*) FROM `product` WHERE (`id` BETWEEN 0 AND 1)', $qs->countSql());

        $qs = Product::objects()->filter(['id__range' => [10, 20]]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals('SELECT COUNT(*) FROM `product` WHERE (`id` BETWEEN 10 AND 20)', $qs->countSql());
    }

    public function testSql()
    {
        $qs = Product::objects()
            ->filter(['name' => 'vasya', 'id__lte' => 7])
            ->filter(['name' => 'petya', 'id__gte' => 3]);

        $this->assertEquals("SELECT COUNT(*) FROM `product` WHERE ((`name`='vasya') AND ((`id` <= 7))) AND ((`name`='petya') AND ((`id` >= 3)))", $qs->countSql());

        $qs = Product::objects()
            ->filter(['name' => 'vasya', 'id__lte' => 2])
            ->orFilter(['name' => 'petya', 'id__gte' => 4]);

        $this->assertEquals("SELECT COUNT(*) FROM `product` WHERE ((`name`='vasya') AND ((`id` <= 2))) OR ((`name`='petya') AND ((`id` >= 4)))", $qs->countSql());
    }

    public function testRecursiveFilters(){

    }
}
