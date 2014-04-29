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
    var $prefix = '';

    public function setUp()
    {
        parent::setUp();

        $this->initModels([new Category, new ProductList, new Product]);

        $model = new Category();
        $this->prefix = $model->getConnection()->tablePrefix;
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
}
