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
use Modules\Tests\Models\Blogger;
use Modules\Tests\Models\Category;
use Modules\Tests\Models\Group;
use Modules\Tests\Models\Membership;
use Modules\Tests\Models\Product;
use Modules\Tests\Models\ProductList;
use Modules\Tests\Models\Project;
use Modules\Tests\Models\ProjectMembership;
use Modules\Tests\Models\User;
use Modules\Tests\Models\Worker;
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
            new Project,
            new ProjectMembership,
            new Worker,
            new Blogger
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

//        // Test empty array
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

    public function testLink()
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

        $this->assertEquals(0, $product->lists->count());
        $product->lists = [$list];
        $product->save();
        $this->assertEquals(1, $product->lists->count());

        $product->lists->unlink($list);
        $product->save();
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

    public function testViaOrder()
    {
        $firstProject = new Project();
        $firstProject->name = 'Building';
        $firstProject->save();

        $secondProject = new Project();
        $secondProject->name = 'Logistic';
        $secondProject->save();

        $firstWorker = new Worker();
        $firstWorker->name = 'Mark';
        $firstWorker->save();

        $secondWorker = new Worker();
        $secondWorker->name = 'Alex';
        $secondWorker->save();

        ProjectMembership::objects()->getOrCreate([
            'project' => $firstProject,
            'worker' => $firstWorker,
            'position' => 1,
            'curator' => $secondWorker
        ]);

        ProjectMembership::objects()->getOrCreate([
            'project' => $firstProject,
            'worker' => $secondWorker,
            'position' => 2,
            'curator' => $firstWorker
        ]);

        $this->assertEquals([
            [
                'id' => '1',
                'project_id' => '1',
                'worker_id' => '1',
                'position' => '1',
                'curator_id' => '2',
            ],
            [
                'id' => '2',
                'project_id' => '1',
                'worker_id' => '2',
                'position' => '2',
                'curator_id' => '1',
            ]
        ], ProjectMembership::objects()->asArray()->all());

        $this->assertEquals([
            [
                'id' => '1',
                'name' => 'Mark',
            ],
            [
                'id' => '2',
                'name' => 'Alex',
            ]
        ], Worker::objects()->filter(['projects__id__in' => [$firstProject->id]])->order(['projects_through__position'])->asArray()->all());

        $this->assertEquals([
            [
                'id' => '2',
                'name' => 'Alex',
            ],
            [
                'id' => '1',
                'name' => 'Mark',
            ]
        ], Worker::objects()->filter(['projects__id__in' => [$firstProject->id]])->order(['-projects_through__position'])->asArray()->all());

        $this->assertEquals([
            [
                'id' => '1',
                'name' => 'Mark',
            ],
            [
                'id' => '2',
                'name' => 'Alex',
            ]
        ], Worker::objects()->order(['projects_through__position'])->asArray()->all());

        $this->assertEquals([
            [
                'id' => '2',
                'name' => 'Alex',
            ],
            [
                'id' => '1',
                'name' => 'Mark',
            ]
        ], Worker::objects()->order(['-projects_through__position'])->asArray()->all());

        $this->assertEquals([
            [
                'id' => '2',
                'name' => 'Alex',
            ]
        ], Worker::objects()->filter(['projects_through__curator' => $firstWorker])->asArray()->all());

        $this->assertEquals([
            [
                'id' => '1',
                'name' => 'Mark',
            ]
        ], Worker::objects()->filter(['projects_through__curator' => $secondWorker])->asArray()->all());

        $this->assertEquals("SELECT `tests_worker_1`.* FROM `tests_worker` `tests_worker_1` LEFT OUTER JOIN `tests_project_membership` `tests_project_membership_2` ON `tests_worker_1`.`id` = `tests_project_membership_2`.`worker_id` LEFT OUTER JOIN `tests_project` `tests_project_3` ON `tests_project_membership_2`.`project_id` = `tests_project_3`.`id` WHERE (`tests_project_membership_2`.`curator_id`='2') GROUP BY `tests_worker_1`.`id`", Worker::objects()->filter(['projects_through__curator' => $secondWorker])->allSql());

        $this->assertEquals("SELECT `tests_worker_1`.* FROM `tests_worker` `tests_worker_1` LEFT OUTER JOIN `tests_project_membership` `tests_project_membership_2` ON `tests_worker_1`.`id` = `tests_project_membership_2`.`worker_id` LEFT OUTER JOIN `tests_project` `tests_project_3` ON `tests_project_membership_2`.`project_id` = `tests_project_3`.`id` GROUP BY `tests_worker_1`.`id` ORDER BY `tests_project_membership_2`.`position`", Worker::objects()->order(['projects_through__position'])->asArray()->allSql());

        $this->assertEquals("SELECT `tests_worker_1`.* FROM `tests_worker` `tests_worker_1` LEFT OUTER JOIN `tests_project_membership` `tests_project_membership_2` ON `tests_worker_1`.`id` = `tests_project_membership_2`.`worker_id` LEFT OUTER JOIN `tests_project` `tests_project_3` ON `tests_project_membership_2`.`project_id` = `tests_project_3`.`id` WHERE (`tests_project_3`.`id` IN ('1', '2')) GROUP BY `tests_worker_1`.`id` ORDER BY `tests_project_membership_2`.`position` DESC", Worker::objects()->filter(['projects__id__in' => [$firstProject->id, $secondProject->id]])->order(['-projects_through__position'])->allSql());
    }

    public function testToSelf()
    {
        $max = new Blogger();
        $max->name = 'Max';
        $max->save();

        $alex = new Blogger();
        $alex->name = 'Alex';
        $alex->save();

        $peter = new Blogger();
        $peter->name = 'Peter';
        $peter->save();

        // Max was subscribed to Peter and Alex
        $max->subscribes = [$peter, $alex];
        $max->save();

        // Alex was subscribed to Peter
        $alex->subscribes = [$peter];
        $alex->save();

        $this->assertEquals(2, $max->subscribes->count());
        $this->assertEquals(0, $max->subscribers->count());

        $this->assertEquals(1, $alex->subscribes->count());
        $this->assertEquals(1, $alex->subscribers->count());

        $this->assertEquals(0, $peter->subscribes->count());
        $this->assertEquals(2, $peter->subscribers->count());

    }
}
