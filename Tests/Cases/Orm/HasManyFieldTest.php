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
use Tests\Models\HasManyModel;
use Tests\Models\FkModel;
use Tests\Models\Product;

class HasManyFieldTest extends DatabaseTestCase
{
    public function getModels()
    {
        return [new Product(), new Category()];
    }

    public function testSimple()
    {
        $category_toys = new Category([
            'name' => 'Toys'
        ]);
        $category_toys->save();

        $category_animals = new Category();
        $category_animals->name = 'Animals';
        $category_animals->save();

        $prefix = $category_toys->getConnection()->tablePrefix;
        $this->assertEquals("SELECT COUNT(*) FROM `{$prefix}product` `product_1` WHERE (`product_1`.`category_id`='1')", $category_toys->products->countSql());
        $this->assertEquals(0, $category_toys->products->count());

        $product_bear = new Product([
            'category' => $category_toys,
            'name' => 'Bear',
            'price' => 100,
            'description' => 'Funny white bear'
        ]);
        $product_bear->save();

        $this->assertEquals(1, $category_toys->products->count());

        $product_rabbit = new Product([
            'category' => $category_animals,
            'name' => 'Rabbit',
            'price' => 110,
            'description' => 'Rabbit with carrot'
        ]);
        $product_rabbit->save();

        $this->assertEquals(1, $category_toys->products->count());

        $product_rabbit->category = $category_toys;
        $product_rabbit->save();

        $this->assertEquals(2, $category_toys->products->count());
    }

    public function testThrough()
    {

    }
}
