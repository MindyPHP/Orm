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

use Mindy\Tests\DatabaseTestCase;
use Tests\Models\Category;
use Tests\Models\Product;
use Tests\Models\ProductList;

class ManyToManyFieldTest extends DatabaseTestCase
{
    public $prefix = '';

    public function setUp()
    {
        parent::setUp();

        $this->dropModels([new Category, new ProductList, new Product]);
        $this->initModels([new Category, new ProductList, new Product]);
    }

    public function tearDown()
    {
        // $this->dropModels([new Category, new ProductList, new Product]);
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
        $this->assertEquals(1, $product->id);

        $list = new ProductList();

        $this->assertTrue($list->getIsNewRecord());

        $list->name = 'qwe';

        $this->assertTrue($list->save());
        $this->assertFalse($list->getIsNewRecord());
        $this->assertEquals(1, $list->id);
        $this->assertEquals(1, $list->pk);

        $list->products->link($product);

        $this->assertEquals(1, ProductList::objects()->count());
        // $this->assertEquals(1, count($product->lists->all()));

        $new = Product::objects()->get(['id' => 1]);
        $this->assertFalse($new->getIsNewRecord());
        $this->assertEquals(1, $new->id);
        $this->assertEquals(1, $new->pk);

        $this->assertEquals(
            "SELECT `tests_product_list_1`.* FROM `tests_product_list` `tests_product_list_1` JOIN `tests_product_tests_product_list` ON `tests_product_tests_product_list`.`product_list_id`=`tests_product_list_1`.`id` WHERE (`tests_product_tests_product_list`.`product_id`='1')",
            $new->lists->allSql());
        $this->assertEquals(1, count($new->lists->all()));
    }
}
