<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests\Fields;

use Mindy\Orm\Tests\Models\Blogger;
use Mindy\Orm\Tests\Models\Category;
use Mindy\Orm\Tests\Models\Group;
use Mindy\Orm\Tests\Models\Membership;
use Mindy\Orm\Tests\Models\Product;
use Mindy\Orm\Tests\Models\ProductList;
use Mindy\Orm\Tests\Models\Project;
use Mindy\Orm\Tests\Models\ProjectMembership;
use Mindy\Orm\Tests\Models\User;
use Mindy\Orm\Tests\Models\Worker;
use Mindy\Orm\Tests\OrmDatabaseTestCase;
use Mindy\QueryBuilder\QueryBuilder;

abstract class ManyToManyFieldTest extends OrmDatabaseTestCase
{
    public $prefix = '';

    protected function getModels()
    {
        return [
            new Category(),
            new ProductList(),
            new Product(),
            new Group(),
            new Membership(),
            new User(),
            new Project(),
            new ProjectMembership(),
            new Worker(),
            new Blogger(),
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

        // TODO тут нужно выбрасывать исключение потому что обращаемся к связанным данным у новой(!) не сохраненной модели
        $this->assertEquals(0, $product->lists->count());
        $this->assertEquals([], $product->lists->all());
        // TODO тут нужно выбрасывать исключение потому что обращаемся к связанным данным у новой(!) не сохраненной модели

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
        $this->assertEquals(1, $product->lists->count());
        $this->assertEquals(1, count($product->lists->all()));

        $new = Product::objects()->get(['id' => 1]);
        $this->assertFalse($new->getIsNewRecord());
        $this->assertEquals(1, $new->id);
        $this->assertEquals(1, $new->pk);

//        $this->assertSql("SELECT [[product_list_1]].* FROM [[product_list]] AS [[product_list_1]] LEFT JOIN [[product_product_list]] AS [[product_product_list_1]] ON [[product_product_list_1]].[[product_list_id]]=[[product_list_1]].[[id]] WHERE ([[product_product_list_1]].[[product_id]]=@1@)",
//            $new->lists->allSql());
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
        $category = new Category(['name' => 'Toys']);
        $this->assertTrue($category->save());

        $product = new Product([
            'name' => 'Bear',
            'price' => 100,
            'description' => 'Funny white bear',
            'category' => $category,
        ]);
        $this->assertTrue($product->save());

        $list = new ProductList(['name' => 'Toys']);
        $this->assertTrue($list->save());

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
        $tableName = QueryBuilder::getInstance($this->getConnection())->getAdapter()->getRawTableName($tableName);
        $cmd = $this->getConnection()->query('SELECT * FROM '.$tableName);
        $all = $cmd->fetchAll();
        $this->assertEquals([], $all);
        $this->assertEquals(0, count($all));

        $product->lists = [$list1];
        $product->save();

        $cmd = $this->getConnection()->query('SELECT * FROM '.$tableName);
        $all = $cmd->fetchAll();
        $this->assertEquals([
            ['product_id' => 1, 'product_list_id' => 1],
        ], $all);
        $this->assertEquals(1, count($all));

        $product->lists = [$list2];
        $product->save();

        $cmd = $this->getConnection()->query("SELECT * FROM {$tableName}");
        $all = $cmd->fetchAll();
        $this->assertEquals([
            ['product_id' => 1, 'product_list_id' => 2],
        ], $all);
        $this->assertEquals(1, count($all));

        $product->lists = [$list1];
        $product->save();

        $cmd = $this->getConnection()->query("SELECT * FROM {$tableName}");
        $all = $cmd->fetchAll();
        $this->assertEquals([
            ['product_id' => 1, 'product_list_id' => 1],
        ], $all);
        $this->assertEquals(1, count($all));

        $product->lists = [$list1, $list1, $list1];
        $product->save();

        $cmd = $this->getConnection()->query("SELECT * FROM {$tableName}");
        $all = $cmd->fetchAll();
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

        (new ProjectMembership([
            'project' => $firstProject,
            'worker' => $firstWorker,
            'position' => 1,
            'curator' => $secondWorker,
        ]))->save();

        (new ProjectMembership([
            'project' => $firstProject,
            'worker' => $secondWorker,
            'position' => 2,
            'curator' => $firstWorker,
        ]))->save();

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
            ],
        ], ProjectMembership::objects()->asArray()->all());

        $qs = Worker::objects()->filter(['projects__id__in' => [$firstProject->id]])->order(['projects__through__position'])->asArray();
        $this->assertSql('SELECT [[worker_1]].* FROM [[worker]] AS [[worker_1]]
LEFT JOIN [[project_membership]] AS [[project_membership_1]] ON [[project_membership_1]].[[worker_id]]=[[worker_1]].[[id]]
LEFT JOIN [[project]] AS [[project_1]] ON [[project_1]].[[id]]=[[project_membership_1]].[[project_id]]
WHERE ([[project_1]].[[id]] IN (@1@))
ORDER BY [[project_membership_1]].[[position]] ASC', $qs->allSql());
        $this->assertEquals([
            ['id' => '1', 'name' => 'Mark'],
            ['id' => '2', 'name' => 'Alex'],
        ], $qs->asArray()->all());

        $this->assertEquals([
            ['id' => '2', 'name' => 'Alex'],
            ['id' => '1', 'name' => 'Mark'],
        ], Worker::objects()->filter(['projects__id__in' => [$firstProject->id]])->order(['-projects__through__position'])->asArray()->all());

        $this->assertEquals([
            ['id' => '1', 'name' => 'Mark'],
            ['id' => '2', 'name' => 'Alex'],
        ], Worker::objects()->order(['projects__through__position'])->asArray()->all());

        $this->assertEquals([
            ['id' => '2', 'name' => 'Alex'],
            ['id' => '1', 'name' => 'Mark'],
        ], Worker::objects()->order(['-projects__through__position'])->asArray()->all());

        $this->assertEquals([
            ['id' => '2', 'name' => 'Alex'],
        ], Worker::objects()->filter(['projects__through__curator' => $firstWorker])->asArray()->all());

        $this->assertEquals([
            ['id' => '1', 'name' => 'Mark'],
        ], Worker::objects()->filter(['projects__through__curator' => $secondWorker])->asArray()->all());

        $this->assertSql('SELECT [[worker_1]].* FROM [[worker]] AS [[worker_1]] LEFT JOIN [[project_membership]] AS [[project_membership_1]] ON [[project_membership_1]].[[worker_id]]=[[worker_1]].[[id]] LEFT JOIN [[project]] AS [[project_1]] ON [[project_1]].[[id]]=[[project_membership_1]].[[project_id]] WHERE ([[project_membership_1]].[[curator_id]]=@2@)',
            Worker::objects()->filter(['projects__through__curator' => $secondWorker])->allSql());
        $this->assertSql('SELECT [[worker_1]].* FROM [[worker]] AS [[worker_1]] LEFT JOIN [[project_membership]] AS [[project_membership_1]] ON [[project_membership_1]].[[worker_id]]=[[worker_1]].[[id]] LEFT JOIN [[project]] AS [[project_1]] ON [[project_1]].[[id]]=[[project_membership_1]].[[project_id]] ORDER BY [[project_membership_1]].[[position]] ASC',
            Worker::objects()->order(['projects__through__position'])->asArray()->allSql());
        $this->assertSql('SELECT [[worker_1]].* FROM [[worker]] AS [[worker_1]] LEFT JOIN [[project_membership]] AS [[project_membership_1]] ON [[project_membership_1]].[[worker_id]]=[[worker_1]].[[id]] LEFT JOIN [[project]] AS [[project_1]] ON [[project_1]].[[id]]=[[project_membership_1]].[[project_id]] WHERE ([[project_1]].[[id]] IN (@1@, @2@)) ORDER BY [[project_membership_1]].[[position]] DESC',
            Worker::objects()->filter(['projects__id__in' => [$firstProject->id, $secondProject->id]])->order(['-projects__through__position'])->allSql());
    }

    public function testToSelf()
    {
        $max = new Blogger(['name' => 'max']);
        $this->assertTrue($max->save());
        $alex = new Blogger(['name' => 'alex']);
        $this->assertTrue($alex->save());
        $peter = new Blogger(['name' => 'peter']);
        $this->assertTrue($peter->save());

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
