<?php

/**
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 17/04/14.04.2014 15:49
 */

namespace Tests\Orm;

use Tests\DatabaseTestCase;
use Tests\Models\Category;
use Tests\Models\Customer;
use Tests\Models\Order;
use Tests\Models\Product;
use Tests\Models\User;

class NullTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->initModels([new Order, new User, new Category, new Customer, new Product]);

        $category = new Category;
        $category->name = 'test';
        $category->save();

        $user = new User;
        $user->password = 123456;
        $user->username = 'example';
        $user->save();

        $customer = new Customer;
        $customer->user = $user;
        $customer->address = 'example super address';
        $customer->save();

        $products = [];
        foreach([1, 2, 3, 4, 5] as $i) {
            $product = new Product;
            $product->name = $i;
            $product->price = $i;
            $product->description = $i;
            $product->category = $category;
            $product->save();
            $products[] = $product;
        }

        $order1 = new Order;
        $order1->customer = $customer;
        $order1->save();

        foreach($products as $p) {
            $order1->products->link($p);
        }

        $order2 = new Order;
        $order2->customer = $customer;
        $order2->discount = 1;
        $order2->save();

        $order2->products = $products;
        $order2->save();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->dropModels([new Order, new User, new Category, new Customer, new Product]);
    }

    public function testIsNull()
    {
        $o1 = Order::objects()->filter(['pk' => 1])->get();
        $o2 = Order::objects()->filter(['pk' => 2])->get();
        $this->assertNull($o1->discount);
        $this->assertNotNull($o2->discount);
    }

    public function testIsNullQuery()
    {
        $this->assertEquals(1, Order::objects()->filter(['discount__isnull' => true])->count());
        $this->assertEquals(1, Order::objects()->filter(['discount__isnull' => false])->count());
    }
}
