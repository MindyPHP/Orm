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
use Mindy\Orm\Tests\Models\Membership;
use Mindy\Orm\Tests\Models\User;
use Mindy\Orm\Tests\OrmDatabaseTestCase;

abstract class OrderByLookupTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new User(), new Group(), new Membership(), new Customer()];
    }

    public function testWithoutLookup()
    {
        $state = (new User(['username' => 'A']))->save();
        $this->assertTrue($state);
        $state = (new User(['username' => 'B']))->save();
        $this->assertTrue($state);

        $users = User::objects()->order(['-username'])->all();
        $this->assertEquals(count($users), 2);
        $this->assertEquals($users[0]->username, 'B');
        $this->assertEquals($users[1]->username, 'A');

        $users = User::objects()->order(['username'])->all();
        $this->assertEquals(count($users), 2);
        $this->assertEquals($users[0]->username, 'A');
        $this->assertEquals($users[1]->username, 'B');
    }

    public function testForeignLookup()
    {
        $state = (new User(['username' => 'A']))->save();
        $this->assertTrue($state);
        $state = (new User(['username' => 'B']))->save();
        $this->assertTrue($state);
        $state = (new User(['username' => 'C']))->save();
        $this->assertTrue($state);

        $state = (new Customer(['address' => 'A', 'user_id' => 1]))->save();
        $this->assertTrue($state);
        $state = (new Customer(['address' => 'B', 'user_id' => 2]))->save();
        $this->assertTrue($state);
        $state = (new Customer(['address' => 'C', 'user_id' => 3]))->save();
        $this->assertTrue($state);

        $addresses = Customer::objects()->order(['-user__username', '-address'])->all();
        $this->assertEquals(count($addresses), 3);
        $this->assertEquals($addresses[0]->address, 'C');
        $this->assertEquals($addresses[1]->address, 'B');
        $this->assertEquals($addresses[2]->address, 'A');

        $qs = Customer::objects()->order(['user__username', '-address']);
        $addresses = $qs->all();
        $this->assertEquals(count($addresses), 3);
        $this->assertEquals($addresses[0]->address, 'A');
        $this->assertEquals($addresses[1]->address, 'B');
        $this->assertEquals($addresses[2]->address, 'C');

        $qs = Customer::objects()->order(['-user__username', 'address']);
        $addresses = $qs->all();
        $this->assertEquals(count($addresses), 3);
        $this->assertEquals($addresses[0]->address, 'C');
        $this->assertEquals($addresses[1]->address, 'B');
        $this->assertEquals($addresses[2]->address, 'A');
    }

    public function testHasManyLookup()
    {
        $state = (new User(['username' => 'A']))->save();
        $this->assertTrue($state);
        $state = (new User(['username' => 'B']))->save();
        $this->assertTrue($state);
        $state = (new User(['username' => 'C']))->save();
        $this->assertTrue($state);

        $state = (new Customer(['address' => 'A', 'user_id' => 1]))->save();
        $this->assertTrue($state);
        $state = (new Customer(['address' => 'B', 'user_id' => 2]))->save();
        $this->assertTrue($state);
        $state = (new Customer(['address' => 'C', 'user_id' => 3]))->save();
        $this->assertTrue($state);

        $qs = User::objects()->order(['-addresses__address']);
        $users = $qs->all();
        $this->assertEquals(3, count($users));
        $this->assertEquals($users[0]->username, 'C');
        $this->assertEquals($users[1]->username, 'B');
        $this->assertEquals($users[2]->username, 'A');

        $addresses = User::objects()->order(['addresses__address'])->all();
        $this->assertEquals(3, count($users));
        $this->assertEquals($addresses[0]->username, 'A');
        $this->assertEquals($addresses[1]->username, 'B');
        $this->assertEquals($addresses[2]->username, 'C');
    }

    public function testManyToManyLookup()
    {
        $userA = new User(['username' => 'A']);
        $this->assertTrue($userA->save());
        $this->assertEquals(1, $userA->pk);
        $groupA = new Group(['name' => 'A']);
        $this->assertTrue($groupA->save());
        $this->assertEquals(1, $groupA->pk);
        $userA->groups->link($groupA);

        $userB = new User(['username' => 'B']);
        $this->assertTrue($userB->save());
        $this->assertEquals(2, $userB->pk);
        $groupB = new Group(['name' => 'B']);
        $this->assertTrue($groupB->save());
        $this->assertEquals(2, $groupB->pk);
        $userB->groups->link($groupB);

        $this->assertEquals(2, User::objects()->count());
        $this->assertEquals(2, Group::objects()->count());

        $this->assertEquals([
            ['id' => '1', 'group_id' => '1', 'user_id' => '1'],
            ['id' => '2', 'group_id' => '2', 'user_id' => '2'],
        ], Membership::objects()->asArray()->all());

//        $this->assertSql(1, $userA->groups->countSql());
        $this->assertEquals(1, $userA->groups->count());
        $this->assertEquals(1, $userB->groups->count());

        $qs = User::objects()->order(['-groups__name']);
        $users = $qs->all();
        $this->assertEquals(count($users), 2);
        $this->assertEquals($users[0]->username, 'B');
        $this->assertEquals($users[1]->username, 'A');

        $addresses = User::objects()->order(['groups__name'])->all();
        $this->assertEquals(count($users), 2);
        $this->assertEquals($addresses[0]->username, 'A');
        $this->assertEquals($addresses[1]->username, 'B');
    }
}
