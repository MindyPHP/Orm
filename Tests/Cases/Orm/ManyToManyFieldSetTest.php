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

class ManyToManyFieldSetTest extends DatabaseTestCase
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

        $product->save();

        $list = new ProductList();
        $list->name = 'Toys';
        $list->save();

        $pk = $list->pk;

        // Test array of Models
        $product->lists = [$list];
        $product->save(['lists']);
        $this->assertEquals(1, $product->lists->count());

        // Test empty array
        $product->lists = [];
        $product->save(['lists']);
        $this->assertEquals(0, $product->lists->count());

        // Test array of pk
        $product->lists = [$pk];
        $product->save(['lists']);
        $this->assertEquals(1, $product->lists->count());

        // Test clean()
        $product->lists->clean();
        $this->assertEquals(0, $product->lists->count());
    }
}
