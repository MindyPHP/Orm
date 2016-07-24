<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 10:32
 */

namespace Mindy\Orm\Tests\QueryBuilder;

use Mindy\Query\Schema\TableSchema;
use Modules\Tests\Models\Customer;
use Modules\Tests\Models\Group;
use Modules\Tests\Models\GroupManager;
use Modules\Tests\Models\Hits;
use Modules\Tests\Models\Membership;
use Modules\Tests\Models\Permission;
use Modules\Tests\Models\User;
use Modules\Tests\Models\UserManager;
use Tests\OrmDatabaseTestCase;

class QuerySetTest extends OrmDatabaseTestCase
{
    public $driver = 'mysql';
    
    public function getModels()
    {
        return [
            new Hits,
            new User,
            new Customer,
            new Group,
            new Permission,
            new Membership
        ];
    }

    public function tearDown()
    {

    }

    public function testInit()
    {
        $tableSchema = $this->connection->getSchema()->getTableSchema(Hits::tableName(), true);
        $this->assertInstanceOf(TableSchema::class, $tableSchema);
    }

    public function testCount()
    {
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $count = Hits::objects()->using($this->connection)->count();
        $this->assertEquals(1, $count);

        $this->assertTrue((new Hits)->using($this->connection)->save());
        $count = Hits::objects()->using($this->connection)->count();
        $this->assertEquals(2, $count);
    }

    public function testFilter()
    {
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $count = Hits::objects()->using($this->connection)->count();
        $this->assertEquals(2, $count);

        $count = Hits::objects()->using($this->connection)->filter(['id' => 1])->count();
        $this->assertEquals(1, $count);
    }

    public function testExclude()
    {
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $count = Hits::objects()->using($this->connection)->count();
        $this->assertEquals(2, $count);

        $count = Hits::objects()->using($this->connection)->exclude(['id' => 1])->count();
        $this->assertEquals(1, $count);
    }

    public function testOrFilter()
    {
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $count = Hits::objects()->using($this->connection)->count();
        $this->assertEquals(3, $count);

        $count = Hits::objects()->using($this->connection)->orFilter(['id' => 2])->filter(['id' => 1])->count();
        $this->assertEquals(2, $count);

        $count = Hits::objects()->using($this->connection)->filter(['id' => 1])->orFilter(['id' => 2])->count();
        $this->assertEquals(2, $count);
    }

    public function testOrExclude()
    {
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $count = Hits::objects()->using($this->connection)->count();
        $this->assertEquals(3, $count);

        $count = Hits::objects()->using($this->connection)->orExclude(['id' => 2])->exclude(['id' => 1])->count();
        $this->assertEquals(1, $count);

        $count = Hits::objects()->using($this->connection)->exclude(['id' => 1])->orExclude(['id' => 2])->count();
        $this->assertEquals(1, $count);
    }

    public function testFetchColumn()
    {
        $count = Hits::objects()->using($this->connection)->filter(['pk__gte' => 2])->count();
        $this->assertEquals(0, $count);
    }

    public function testLookup()
    {
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $this->assertTrue((new Hits)->using($this->connection)->save());
        $count = Hits::objects()->using($this->connection)->filter(['pk__gte' => 2])->count();
        $this->assertEquals(2, $count);

        $count = Hits::objects()->using($this->connection)->filter(['pk__gt' => 2])->count();
        $this->assertEquals(1, $count);

        $count = Hits::objects()->using($this->connection)->filter(['id__in' => [1, 3]])->count();
        $this->assertEquals(2, $count);
    }

    public function testCallback()
    {
        $this->assertTrue((new User(['username' => 'foo', 'password' => 'bar']))->using($this->connection)->save());
        $count = User::objects()->using($this->connection)->count();
        $this->assertEquals(1, $count);

        $this->assertTrue((new Customer(['address' => 'foo', 'user_id' => 1]))->using($this->connection)->save());
        $count = Customer::objects()->using($this->connection)->count();
        $this->assertEquals(1, $count);

        $count = User::objects()->using($this->connection)->filter(['addresses__address' => 'foo'])->count();
        $this->assertEquals(1, $count);

        $this->assertTrue((new Group(['name' => 'foo']))->using($this->connection)->save());
        $count = Group::objects()->using($this->connection)->count();
        $this->assertEquals(1, $count);

        $group = Group::objects()->using($this->connection)->get(['id' => 1]);
        $user = User::objects()->using($this->connection)->get(['id' => 1]);
        $user->groups->link($group);

        $count = User::objects()->using($this->connection)->filter(['groups__name' => 'foo'])->count();
        $this->assertEquals(1, $count);

        $count = User::objects()->using($this->connection)->filter(['groups__name' => 'bar'])->count();
        $this->assertEquals(0, $count);

        $count = User::objects()->using($this->connection)->filter([
            'groups__name' => 'foo',
            'addresses__address' => 'foo',
            'password' => 'bar'
        ])->count();
        $this->assertEquals(1, $count);
    }

    public function testForeignField()
    {
        $this->assertTrue((new User(['username' => 'foo', 'password' => 'bar']))->using($this->connection)->save());
        $count = User::objects()->using($this->connection)->count();
        $this->assertEquals(1, $count);

        $this->assertTrue((new Customer(['address' => 'foo', 'user_id' => 1]))->using($this->connection)->save());
        $count = Customer::objects()->using($this->connection)->count();
        $this->assertEquals(1, $count);

        $count = Customer::objects()->using($this->connection)->filter([
            'user__id__gt' => 1
        ])->count();
        $this->assertEquals(0, $count);

        $count = Customer::objects()->using($this->connection)->filter([
            'user__id' => 1
        ])->count();
        $this->assertEquals(1, $count);
    }

    public function testManager()
    {
        $user = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user->using($this->connection)->save());
        $customer = new Customer(['address' => 'foo', 'user_id' => 1]);
        $this->assertTrue($customer->using($this->connection)->save());
        $permission = new Permission(['code' => 'foo']);
        $this->assertTrue($permission->using($this->connection)->save());

        $this->assertInstanceOf(GroupManager::class, $user->groups);
        $this->assertTrue(method_exists($user->groups, 'published'));
        $this->assertTrue(method_exists($user->groups, 'link'));
        $this->assertTrue(method_exists($user->groups, 'unlink'));

        $this->assertInstanceOf(GroupManager::class, $permission->groups);
        $this->assertTrue(method_exists($user->groups, 'published'));
        $this->assertTrue(method_exists($user->groups, 'link'));
        $this->assertTrue(method_exists($user->groups, 'unlink'));
    }
}