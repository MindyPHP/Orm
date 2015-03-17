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

use Mindy\Query\ConnectionManager;
use Tests\Models\Category;
use Tests\Models\Group;
use Tests\Models\Membership;
use Tests\Models\Product;
use Tests\Models\ProductList;
use Tests\Models\User;
use Tests\OrmDatabaseTestCase;

abstract class ManyToManyFieldTest extends OrmDatabaseTestCase
{
    public $prefix = '';

    public $manySql = "SELECT `tests_product_list_2`.* FROM `tests_product_list` `tests_product_list_2` JOIN `tests_product_tests_product_list` `tests_product_tests_product_list_1` ON `tests_product_tests_product_list_1`.`product_list_id`=`tests_product_list_2`.`id` WHERE (`tests_product_tests_product_list_1`.`product_id`='1')";

    protected function getModels()
    {
        return [
            new Category,
            new ProductList,
            new Product,
            new Group,
            new Membership,
            new User,
        ];
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
        $this->assertEquals(1, count($product->lists->all()));

        $new = Product::objects()->get(['id' => 1]);
        $this->assertFalse($new->getIsNewRecord());
        $this->assertEquals(1, $new->id);
        $this->assertEquals(1, $new->pk);

        $this->assertEquals($this->manySql, $new->lists->allSql());
        $this->assertEquals(1, count($new->lists->all()));
    }

    public function testVia()
    {
        $group = new Group();
        $group->name = 'Administrators';
        $this->assertNull($group->pk);
        $this->assertInstanceOf('\Mindy\Orm\ManyToManyManager', $group->users);
        $this->assertEquals(0, $group->users->count());
        $this->assertEquals([], $group->users->all());

        $this->assertTrue($group->save());
        $this->assertEquals(1, $group->pk);

        $user = new User();
        $user->username = 'Anton';
        $user->password = 'VeryGoodP@ssword';
        $this->assertTrue($user->save());

        $this->assertEquals(0, count($group->users->all()));

        $group->users->link($user);
        $this->assertEquals(1, count($group->users->all()));

        $new = Group::objects()->get(['id' => 1]);
        $this->assertEquals(1, count($new->users->all()));

        $memberships = Membership::objects()->filter(['group_id' => 1, 'user_id' => 1])->all();
        $this->assertEquals(1, count($memberships));
    }

    public function testSet()
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

    public function testCross()
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

    public function testExtraLinkRecords()
    {
        $product = new Product();
        $product->name = 'Bear';
        $product->price = 100;
        $product->description = 'Funny white bear';
        $product->save();

        $list1 = new ProductList();
        $list1->name = 'Toys';
        $list1->save();

        $list2 = new ProductList();
        $list2->name = 'Trash';
        $list2->save();

        $this->assertEquals(1, Product::objects()->count());
        $this->assertEquals(2, ProductList::objects()->count());
        $tableName = $product->getField('lists')->getTableName();
        $cmd = ConnectionManager::getDb()->createCommand("SELECT * FROM {$tableName}");
        $all = $cmd->queryAll();
        $this->assertEquals([], $all);
        $this->assertEquals(0, count($all));

        $product->lists = [$list1];
        $product->save();

        $cmd = ConnectionManager::getDb()->createCommand("SELECT * FROM {$tableName}");
        $all = $cmd->queryAll();
        $this->assertEquals([
            ['product_id' => 1, 'product_list_id' => 1]
        ], $all);
        $this->assertEquals(1, count($all));

        $product->lists = [$list2];
        $product->save();

        $cmd = ConnectionManager::getDb()->createCommand("SELECT * FROM {$tableName}");
        $all = $cmd->queryAll();
        $this->assertEquals([
            ['product_id' => 1, 'product_list_id' => 2]
        ], $all);
        $this->assertEquals(1, count($all));

        $product->lists = [$list1];
        $product->save();

        $cmd = ConnectionManager::getDb()->createCommand("SELECT * FROM {$tableName}");
        $all = $cmd->queryAll();
        $this->assertEquals([
            ['product_id' => 1, 'product_list_id' => 1]
        ], $all);
        $this->assertEquals(1, count($all));

        $product->lists = [$list1, $list1, $list1];
        $product->save();

        $cmd = ConnectionManager::getDb()->createCommand("SELECT * FROM {$tableName}");
        $all = $cmd->queryAll();
        $this->assertEquals([
            ['product_id' => 1, 'product_list_id' => 1],
            ['product_id' => 1, 'product_list_id' => 1],
            ['product_id' => 1, 'product_list_id' => 1],
        ], $all);
        $this->assertEquals(3, count($all));
    }
}
