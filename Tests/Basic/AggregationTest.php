<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests\Basic;

use Mindy\Orm\Tests\Models\Customer;
use Mindy\Orm\Tests\Models\Group;
use Mindy\Orm\Tests\Models\Membership;
use Mindy\Orm\Tests\Models\User;
use Mindy\Orm\Tests\OrmDatabaseTestCase;

abstract class AggregationTest extends OrmDatabaseTestCase
{
    public function getModels()
    {
        return [new User(), new Group(), new Customer(), new Membership()];
    }

    public function testMax()
    {
        $this->assertTrue((new User(['username' => 'A']))->save());
        $this->assertTrue((new User(['username' => 'B']))->save());
        $this->assertEquals(2, User::objects()->max('id'));
        $this->assertEquals(1, User::objects()->filter(['username' => 'A'])->max('id'));
    }

    public function testMin()
    {
        $this->assertTrue((new User(['username' => 'A']))->save());
        $this->assertTrue((new User(['username' => 'B']))->save());
        $this->assertEquals(1, User::objects()->min('id'));
        $this->assertEquals(2, User::objects()->filter(['username' => 'B'])->min('id'));
    }

    public function testAverage()
    {
        $this->assertTrue((new User(['username' => 'A']))->save());
        $this->assertTrue((new User(['username' => 'AB']))->save());
        $this->assertEquals(1.5, User::objects()->average('id'));
        $this->assertEquals(1, User::objects()->filter(['username' => 'A'])->average('id'));
    }

    public function testSum()
    {
        $this->assertTrue((new User(['username' => 'A']))->save());
        $this->assertTrue((new User(['username' => 'B']))->save());
        $this->assertEquals(3, User::objects()->sum('id'));
        $this->assertEquals(1, User::objects()->filter(['username' => 'A'])->sum('id'));
    }

    public function testCount()
    {
        $this->assertTrue((new User(['username' => 'A']))->save());
        $this->assertTrue((new User(['username' => 'B']))->save());
        $this->assertEquals(2, User::objects()->count('id'));
        $this->assertEquals(1, User::objects()->filter(['username' => 'A'])->count('id'));
    }

    private function init()
    {
        $group = new Group(['name' => 'Administrators']);
        $this->assertTrue($group->save());
        $this->assertEquals(1, $group->id);
        $this->assertEquals(1, $group->pk);

        $groupDev = new Group(['name' => 'Programmers']);
        $this->assertTrue($groupDev->save());
        $this->assertEquals(2, $groupDev->pk);

        $user = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user->save());
        $groupDev->users->link($user);

        $customer = new Customer(['address' => 'home', 'user' => $user]);
        $this->assertTrue($customer->save());

        $customer2 = new Customer();
        $customer2->address = 'Anton work';
        $customer2->user = $user;
        $this->assertTrue($customer2->save());

        $max = new User();
        $max->username = 'qwe';
        $max->password = 'qwerty';
        $this->assertTrue($max->save());

        $group->users->link($max);
        $groupDev->users->link($max);

        $customer3 = new Customer();
        $customer3->address = 'Max home';
        $customer3->user = $max;
        $customer3->save();
    }

    public function testMinMore()
    {
        $user = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user->save());
        $customer = new Customer(['address' => 'abc', 'user' => $user]);
        $this->assertTrue($customer->save());

        $this->assertEquals(1, User::objects()->min('id'));
        $this->assertEquals(1, User::objects()->filter(['addresses__address__startswith' => 'a'])->min('id'));
        $this->assertEquals(1, User::objects()->filter(['addresses__address__startswith' => 'a'])->min('id'));
        $this->assertEquals(1, User::objects()->filter(['addresses__address__startswith' => 'a'])->min('addresses__id'));
    }

    public function testMaxMore()
    {
        $user1 = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user1->save());
        $user2 = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user2->save());
        $customer = new Customer(['address' => 'abc', 'user' => $user1]);
        $this->assertTrue($customer->save());
        $customer = new Customer(['address' => 'bca', 'user' => $user2]);
        $this->assertTrue($customer->save());

        $this->assertEquals(2, User::objects()->max('id'));
        $this->assertEquals(1, User::objects()->filter(['addresses__address__startswith' => 'a'])->max('id'));
        $this->assertEquals(1, User::objects()->filter(['addresses__address__startswith' => 'a'])->max('addresses__id'));
    }

    public function testAvgMore()
    {
        $user1 = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user1->save());
        $user2 = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user2->save());
        $customer = new Customer(['address' => 'abc', 'user' => $user1]);
        $this->assertTrue($customer->save());
        $customer = new Customer(['address' => 'bca', 'user' => $user2]);
        $this->assertTrue($customer->save());

        $this->assertEquals(1.5, User::objects()->average('id'));
        $this->assertEquals(1, User::objects()->filter(['addresses__address__startswith' => 'a'])->average('id'));
        $this->assertEquals(1, User::objects()->filter(['addresses__address__startswith' => 'a'])->average('addresses__id'));
    }

    public function testSumMore()
    {
        $user1 = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user1->save());
        $user2 = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user2->save());
        $customer = new Customer(['address' => 'abc', 'user' => $user1]);
        $this->assertTrue($customer->save());
        $customer = new Customer(['address' => 'abc', 'user' => $user2]);
        $this->assertTrue($customer->save());

        $this->assertEquals(User::objects()->sum('id'), 3);
        $this->assertEquals(User::objects()->filter(['addresses__address__startswith' => 'a'])->sum('addresses__id'), 3);
    }
}
