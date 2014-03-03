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


use Mindy\Orm\LookupBuilder;
use Tests\DatabaseTestCase;
use Tests\Models\Category;
use Tests\Models\Customer;
use Tests\Models\Order;
use Tests\Models\Product;
use Tests\Models\User;


class LookupTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([
            new Order,
            new User,
            new Customer,
            new Product,
            new Category
        ]);

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

        $order = new Order;
        $order->customer = $customer;
        $order->save();

        $order->products = $products;
        $order->save();
    }

    public function tearDown()
    {
        $this->dropModels([
            new Order,
            new User,
            new Customer,
            new Product,
            new Category
        ]);
    }

    public function testInit()
    {
        $this->assertEquals(5, Product::objects()->count());
        $this->assertEquals(1, Category::objects()->count());
        $this->assertEquals(1, User::objects()->count());
        $this->assertEquals(1, Customer::objects()->count());
        $this->assertEquals(1, Order::objects()->count());
        $this->assertEquals(5, Order::objects()->get(['pk' => 1])->products->count());
    }

    public function testIn()
    {
        $query = ['items__user__pages__pk__in' => [1, 2, 3]];
        $lookup = new LookupBuilder($query);
        $this->assertEquals([
            [
                ['items', 'user', 'pages'],
                'pk',
                'in',
                [1, 2, 3]
            ]
        ], $lookup->parse());
    }

    public function testSimple()
    {
        $query = ['items__user__pages__pk' => 1];
        $lookup = new LookupBuilder($query);
        $this->assertEquals([
            [
                ['items', 'user', 'pages'],
                'pk',
                'exact',
                1
            ]
        ], $lookup->parse());
    }

    public function testInSimple()
    {
        $query = ['category__in' => [1, 2, 3, 4, 5]];
        $lookup = new LookupBuilder($query);
        $this->assertEquals([
            [
                [],
                'category',
                'in',
                [1, 2, 3, 4, 5]
            ]
        ], $lookup->parse());
    }
}
