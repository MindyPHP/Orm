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

class ManyToManyFieldCrossTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new Category(), new Product(), new ProductList()]);
    }

    public function tearDown()
    {
        $this->dropModels([new Category(), new Product(), new ProductList()]);
    }

    public function testSimple()
    {
        $category = new Category();
        $category->name = 'Toys';
        $category->save();

        $product_bear = new Product();
        $product_bear->category = $category;
        $product_bear->name = 'Bear';
        $product_bear->price = 100;
        $product_bear->description = 'Funny white bear';
        $product_bear->save();

        $product_rabbit = new Product();
        $product_rabbit->category = $category;
        $product_rabbit->name = 'Rabbit';
        $product_rabbit->price = 110;
        $product_rabbit->description = 'Rabbit with carrot';
        $product_rabbit->save();

        $this->assertInstanceOf('\Mindy\Orm\ManyToManyManager', $product_rabbit->lists);
        $this->assertEquals(0, $product_rabbit->lists->count());

        $best_sellers = new ProductList();
        $best_sellers->name = 'Best sellers';
        $best_sellers->save();

        $this->assertEquals(0, $best_sellers->products->count());

        $best_sellers->products->link($product_rabbit);

        $this->assertEquals(1, $best_sellers->products->count());
        $this->assertEquals(1, $product_rabbit->lists->count());

        $product_bear->lists->link($best_sellers);

        $this->assertEquals(2, $best_sellers->products->count());
        $this->assertEquals(1, $product_bear->lists->count());
    }
}
