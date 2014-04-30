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
use Tests\Models\ProductList;


class LookupTest extends DatabaseTestCase
{
    var $prefix = '';

    public function setUp()
    {
        parent::setUp();

        $this->initModels([
            new Order,
            new User,
            new Customer,
            new Product,
            new Category,
            new ProductList
        ]);

        $category = new Category;
        $category->name = 'test';
        $category->save();

        $product_list = new ProductList();
        $product_list->name = 'First product list';
        $product_list->date_action = '2014-04-29 10:35:45';
        $product_list->save();

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

        $model = new Category();
        $this->prefix = $model->getConnection()->tablePrefix;
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

    public function testExact()
    {
        $qs = Product::objects()->filter(['id' => 2]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(1, $qs->count());
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE (`product_1`.`id`=2)", $qs->countSql());
    }

    public function testIsNull()
    {
        $qs = Product::objects()->filter(['id__isnull' => true]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(0, $qs->count());
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE (`product_1`.`id` IS NULL)", $qs->countSql());
    }

    public function testIn()
    {
        $qs = Product::objects()->filter(['category__in' => [1, 2, 3, 4, 5]]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE (`product_1`.`category` IN (1, 2, 3, 4, 5))", $qs->countSql());
    }

    public function testGte()
    {
        $qs = Product::objects()->filter(['id__gte' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE ((`product_1`.`id` >= 1))", $qs->countSql());
    }

    public function testGt()
    {
        $qs = Product::objects()->filter(['id__gt' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE ((`product_1`.`id` > 1))", $qs->countSql());
    }

    public function testLte()
    {
        $qs = Product::objects()->filter(['id__lte' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE ((`product_1`.`id` <= 1))", $qs->countSql());
    }

    public function testLt()
    {
        $qs = Product::objects()->filter(['id__lt' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE ((`product_1`.`id` < 1))", $qs->countSql());
    }

    public function testContains()
    {
        $qs = Product::objects()->filter(['id__contains' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE (`product_1`.`id` LIKE '%1%')", $qs->countSql());
    }

    public function testStartswith()
    {
        $qs = Product::objects()->filter(['id__startswith' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE (`product_1`.`id` LIKE '1%')", $qs->countSql());
    }

    public function testEndswith()
    {
        $qs = Product::objects()->filter(['id__endswith' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE (`product_1`.`id` LIKE '%1')", $qs->countSql());
    }

    public function testYear()
    {
        $qs = ProductList::objects()->filter(['date_action__year' => 2014]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((EXTRACT(YEAR FROM `product_list_1`.`date_action`) = '2014'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__year' => '2013']);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((EXTRACT(YEAR FROM `product_list_1`.`date_action`) = '2013'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testMonth()
    {
        $qs = ProductList::objects()->filter(['date_action__month' => 4]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((EXTRACT(MONTH FROM `product_list_1`.`date_action`) = '4'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__month' => '3']);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((EXTRACT(MONTH FROM `product_list_1`.`date_action`) = '3'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testDay()
    {
        $qs = ProductList::objects()->filter(['date_action__day' => 29]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((EXTRACT(DAY FROM `product_list_1`.`date_action`) = '29'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__day' => '30']);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((EXTRACT(DAY FROM `product_list_1`.`date_action`) = '30'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testWeekDay()
    {
        $qs = ProductList::objects()->filter(['date_action__week_day' => 3]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((DAYOFWEEK(`product_list_1`.`date_action`) = '3'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__week_day' => '4']);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((DAYOFWEEK(`product_list_1`.`date_action`) = '4'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testHour()
    {
        $qs = ProductList::objects()->filter(['date_action__hour' => 10]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((EXTRACT(HOUR FROM `product_list_1`.`date_action`) = '10'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__hour' => '11']);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((EXTRACT(HOUR FROM `product_list_1`.`date_action`) = '11'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testMinute()
    {
        $qs = ProductList::objects()->filter(['date_action__minute' => 35]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((EXTRACT(MINUTE FROM `product_list_1`.`date_action`) = '35'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__minute' => '36']);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((EXTRACT(MINUTE FROM `product_list_1`.`date_action`) = '36'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testSecond()
    {
        $qs = ProductList::objects()->filter(['date_action__second' => 45]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((EXTRACT(SECOND FROM `product_list_1`.`date_action`) = '45'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__second' => '46']);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((EXTRACT(SECOND FROM `product_list_1`.`date_action`) = '46'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testRange()
    {
        $qs = Product::objects()->filter(['id__range' => [0, 1]]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE (`product_1`.`id` BETWEEN 0 AND 1)", $qs->countSql());

        $qs = Product::objects()->filter(['id__range' => [10, 20]]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE (`product_1`.`id` BETWEEN 10 AND 20)", $qs->countSql());
    }

    public function testRegex()
    {
        $qs = ProductList::objects()->filter(['name__regex' => '[a-z]']);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((`product_list_1`.`name` REGEXP BINARY '[a-z]'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['name__regex' => '[0-9]']);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((`product_list_1`.`name` REGEXP BINARY '[0-9]'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testIregex()
    {
        $qs = ProductList::objects()->filter(['name__iregex' => '[P-Z]']);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((`product_list_1`.`name` REGEXP '[P-Z]'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['name__iregex' => '[0-9]']);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product_list` `product_list_1` WHERE ((`product_list_1`.`name` REGEXP '[0-9]'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testSql()
    {
        $qs = Product::objects()
            ->filter(['name' => 'vasya', 'id__lte' => 7])
            ->filter(['name' => 'petya', 'id__gte' => 3]);

        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE ((`product_1`.`name`='vasya') AND ((`product_1`.`id` <= 7))) AND ((`product_1`.`name`='petya') AND ((`product_1`.`id` >= 3)))", $qs->countSql());

        $qs = Product::objects()
            ->filter(['name' => 'vasya', 'id__lte' => 2])
            ->orFilter(['name' => 'petya', 'id__gte' => 4]);

        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE ((`product_1`.`name`='vasya') AND ((`product_1`.`id` <= 2))) OR ((`product_1`.`name`='petya') AND ((`product_1`.`id` >= 4)))", $qs->countSql());
    }

    public function testAllSql()
    {
        $qs = Product::objects()->filter(['id' => 1]);
        $this->assertEquals("SELECT `product_1`.* FROM `{$this->prefix}product` `product_1` WHERE (`product_1`.`id`=1)", $qs->getSql());
        $this->assertEquals("SELECT `product_1`.* FROM `{$this->prefix}product` `product_1` WHERE (`product_1`.`id`=1)", $qs->allSql());
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}product` `product_1` WHERE (`product_1`.`id`=1)", $qs->countSql());
    }
}
