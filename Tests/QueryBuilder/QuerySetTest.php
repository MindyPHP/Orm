<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests\QueryBuilder;

use Mindy\Orm\Tests\Models\Customer;
use Mindy\Orm\Tests\Models\Group;
use Mindy\Orm\Tests\Models\GroupManager;
use Mindy\Orm\Tests\Models\Hits;
use Mindy\Orm\Tests\Models\Membership;
use Mindy\Orm\Tests\Models\Permission;
use Mindy\Orm\Tests\Models\User;
use Mindy\Orm\Tests\OrmDatabaseTestCase;

class QuerySetTest extends OrmDatabaseTestCase
{
    public $driver = 'mysql';

    public function getModels()
    {
        return [
            new Hits(),
            new User(),
            new Customer(),
            new Group(),
            new Permission(),
            new Membership(),
        ];
    }

    public function testCount()
    {
        $this->assertTrue((new Hits())->save());
        $count = Hits::objects()->count();
        $this->assertEquals(1, $count);

        $this->assertTrue((new Hits())->save());
        $count = Hits::objects()->count();
        $this->assertEquals(2, $count);
    }

    public function testValuesList()
    {
        $this->assertTrue((new Hits())->save());
        $count = Hits::objects()->count();
        $this->assertEquals(1, $count);

        $ids = Hits::objects()->valuesList(['pk'], true);
        $this->assertEquals([1], $ids);
    }

    public function testFilter()
    {
        $this->assertTrue((new Hits())->save());
        $this->assertTrue((new Hits())->save());
        $count = Hits::objects()->count();
        $this->assertEquals(2, $count);

        $count = Hits::objects()->filter(['id' => 1])->count();
        $this->assertEquals(1, $count);
    }

    public function testExclude()
    {
        $this->assertTrue((new Hits())->save());
        $this->assertTrue((new Hits())->save());
        $count = Hits::objects()->count();
        $this->assertEquals(2, $count);

        $count = Hits::objects()->exclude(['id' => 1])->count();
        $this->assertEquals(1, $count);
    }

    public function testOrFilter()
    {
        $this->assertTrue((new Hits())->save());
        $this->assertTrue((new Hits())->save());
        $this->assertTrue((new Hits())->save());
        $count = Hits::objects()->count();
        $this->assertEquals(3, $count);

        $count = Hits::objects()->orFilter(['id' => 2])->filter(['id' => 1])->count();
        $this->assertEquals(2, $count);

        $count = Hits::objects()->filter(['id' => 1])->orFilter(['id' => 2])->count();
        $this->assertEquals(2, $count);
    }

    public function testOrExclude()
    {
        $this->assertTrue((new Hits())->save());
        $this->assertTrue((new Hits())->save());
        $this->assertTrue((new Hits())->save());
        $count = Hits::objects()->count();
        $this->assertEquals(3, $count);

        $qs = Hits::objects()->orExclude(['id' => 2])->exclude(['id' => 1]);
        $count = $qs->count();
        $sql = $qs->countSql();
        $this->assertSql('SELECT COUNT(*) FROM [[hits]] AS [[hits_1]] WHERE ((NOT ([[hits_1]].[[id]]=1))) OR ((NOT ([[hits_1]].[[id]]=2)))', $sql);
        $this->assertEquals(3, $count);

        $count = Hits::objects()->exclude(['id' => 1])->orExclude(['id' => 2])->count();
        $this->assertEquals(3, $count);
    }

    public function testFetchColumn()
    {
        $count = Hits::objects()->filter(['pk__gte' => 2])->count();
        $this->assertEquals(0, $count);
    }

    public function testLookup()
    {
        $this->assertTrue((new Hits())->save());
        $this->assertTrue((new Hits())->save());
        $this->assertTrue((new Hits())->save());
        $count = Hits::objects()->filter(['pk__gte' => 2])->count();
        $this->assertEquals(2, $count);

        $count = Hits::objects()->filter(['pk__gt' => 2])->count();
        $this->assertEquals(1, $count);

        $count = Hits::objects()->filter(['id__in' => [1, 3]])->count();
        $this->assertEquals(2, $count);
    }

    public function testCallback()
    {
        $this->assertTrue((new User(['username' => 'foo', 'password' => 'bar']))->save());
        $count = User::objects()->count();
        $this->assertEquals(1, $count);

        $this->assertTrue((new Customer(['address' => 'foo', 'user_id' => 1]))->save());
        $count = Customer::objects()->count();
        $this->assertEquals(1, $count);

        $count = User::objects()->filter(['addresses__address' => 'foo'])->count();
        $this->assertEquals(1, $count);

        $this->assertTrue((new Group(['name' => 'foo']))->save());
        $count = Group::objects()->count();
        $this->assertEquals(1, $count);

        $group = Group::objects()->get();
        $this->assertNotNull($group);
        $this->assertInstanceOf(Group::class, $group);
        $user = User::objects()->get();
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(GroupManager::class, $user->groups);
        /** @var GroupManager $groupManager */
        $groupManager = $user->groups;
        $groupManager->link($group);

        $count = User::objects()->filter(['groups__name' => 'foo'])->count();
        $this->assertEquals(1, $count);

        $count = User::objects()->filter(['groups__name' => 'bar'])->count();
        $this->assertEquals(0, $count);

        $count = User::objects()->filter([
            'groups__name' => 'foo',
            'addresses__address' => 'foo',
            'password' => 'bar',
        ])->count();
        $this->assertEquals(1, $count);
    }

    public function testFind()
    {
        $this->assertTrue((new User(['username' => 'foo', 'password' => 'bar']))->save());
        $this->assertTrue((new User(['username' => 'foo', 'password' => 'bar']))->save());
        $qs = User::objects();
        $this->assertEquals(2, $qs->count());
        $this->assertEquals([[
            'id' => 1,
            'username' => 'foo',
            'password' => 'bar',
        ], [
            'id' => 2,
            'username' => 'foo',
            'password' => 'bar',
        ]], $qs->asArray()->all());
    }

    public function testMultipleQuery()
    {
        $this->assertSql('SELECT COUNT(*) FROM [[users]] AS [[users_1]]', User::objects()->countSql());
        $this->assertSql('SELECT [[users_1]].* FROM [[users]] AS [[users_1]]', User::objects()->allSql());

        $this->assertSql('SELECT [[users_1]].* FROM [[users]] AS [[users_1]]', User::objects()->allSql());
        $this->assertSql('SELECT COUNT(*) FROM [[users]] AS [[users_1]]', User::objects()->countSql());
    }

    public function testForeignField()
    {
        $this->assertTrue((new User(['username' => 'foo', 'password' => 'bar']))->save());
        $count = User::objects()->count();
        $this->assertEquals(1, $count);

        $this->assertTrue((new Customer(['address' => 'foo', 'user_id' => 1]))->save());
        $count = Customer::objects()->count();
        $this->assertEquals(1, $count);

        $count = Customer::objects()->filter(['user__id__gt' => 1])->count();
        $this->assertEquals(0, $count);

        $qs = Customer::objects()->filter(['user__id' => 1]);
        $count = $qs->count();
        $this->assertEquals(1, $count);
    }

    public function testManager()
    {
        $user = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user->save());
        $customer = new Customer(['address' => 'foo', 'user_id' => 1]);
        $this->assertTrue($customer->save());
        $permission = new Permission(['code' => 'foo']);
        $this->assertTrue($permission->save());

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
