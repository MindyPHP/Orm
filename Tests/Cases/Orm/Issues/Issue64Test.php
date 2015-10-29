<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 16/02/15 17:38
 */

namespace Tests\Cases\Orm\Issues;

use Modules\Tests\Models\Category;
use Modules\Tests\Models\Product;
use Modules\Tests\Models\ProductList;
use Tests\OrmDatabaseTestCase;

class Issue64Test extends OrmDatabaseTestCase
{
    public $driver = 'sqlite';

    public function getModels()
    {
        return [new Product, new Category, new ProductList];
    }

    public function setUp()
    {
        parent::setUp();

        $category = new Category([
            'name' => 'cat'
        ]);
        $category->save();

        $product1 = new Product([
            'name' => 'foo',
            'category' => $category
        ]);
        $product1->save();

        $product2 = new Product([
            'name' => 'bar',
            'category' => $category
        ]);
        $product2->save();

        $list = new ProductList([
            'name' => 'test'
        ]);
        $list->save();
        $list->products->link($product1);
        $list->products->link($product2);

        $list = new ProductList([
            'name' => 'asd'
        ]);
        $list->save();
        $list->products->link($product1);
    }

    public function testIssue64()
    {
        $this->assertEquals(1, Category::objects()->count());
        $this->assertEquals(2, Product::objects()->count());
        $this->assertEquals(2, ProductList::objects()->count());
        $category = Category::objects()->get();
        $this->assertEquals(2, $category->products->count());
        $this->assertEquals(1, $category->products->filter(['name' => 'bar'])->count());

        $this->assertEquals(2, $category->products->filter(['lists__name' => 'test'])->count());
        $this->assertEquals(1, $category->products->filter(['lists__name' => 'asd'])->count());
        $this->assertEquals("SELECT `tests_product_1`.* FROM `tests_product` `tests_product_1` LEFT OUTER JOIN `tests_product_tests_product_list` `tests_product_tests_product_list_2` ON `tests_product_1`.`id` = `tests_product_tests_product_list_2`.`product_id` LEFT OUTER JOIN `tests_product_list` `tests_product_list_3` ON `tests_product_tests_product_list_2`.`product_list_id` = `tests_product_list_3`.`id` WHERE ((`tests_product_1`.`category_id`='1')) AND ((`tests_product_list_3`.`name`='asd')) GROUP BY `tests_product_1`.`id`",
            $category->products->filter(['lists__name' => 'asd'])->allSql());
    }
}
