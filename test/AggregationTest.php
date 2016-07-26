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

namespace Mindy\Orm\Tests;

use Modules\Tests\Models\Customer;
use Modules\Tests\Models\Group;
use Modules\Tests\Models\Membership;
use Modules\Tests\Models\User;
use Tests\OrmDatabaseTestCase;

abstract class AggregationTest extends OrmDatabaseTestCase
{
    public function getModels()
    {
        return [
            new User,
            new Group,
            new Membership,
            new Customer
        ];
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
        $customer2->address = "Anton work";
        $customer2->user = $user;
        $this->assertTrue($customer2->save());

        $max = new User();
        $max->username = 'qwe';
        $max->password = 'qwerty';
        $this->assertTrue($max->save());

        $group->users->link($max);
        $groupDev->users->link($max);

        $customer3 = new Customer();
        $customer3->address = "Max home";
        $customer3->user = $max;
        $customer3->save();
    }

    public function testMin()
    {
        $this->assertEquals(1, User::objects()->min('id'));
        $this->assertEquals(1, User::objects()->filter(['addresses__address__startswith' => 'A'])->min('id'));
        $this->assertEquals(1, User::objects()->filter(['addresses__address__startswith' => 'A'])->min('addresses__id'));
    }

    public function testMax()
    {
        $this->assertEquals(2, User::objects()->max('id'));
        $this->assertEquals(2, User::objects()->filter(['addresses__address__startswith' => 'A'])->max('id'));
        $this->assertEquals(2, User::objects()->filter(['addresses__address__startswith' => 'A'])->max('addresses__id'));
    }

    public function testAvg()
    {
        $this->assertEquals(1.5, User::objects()->average('id'));
        $this->assertEquals(1, User::objects()->filter(['addresses__address__startswith' => 'A'])->average('id'));
        $this->assertEquals(1.5, User::objects()->filter(['addresses__address__startswith' => 'A'])->average('addresses__id'));
    }

    public function testSum()
    {
        $this->init();
        $this->assertEquals(User::objects()->sum('id'), 3);
        $this->assertEquals(User::objects()->filter(['addresses__address__startswith' => 'A'])->sum('addresses__id'), 3);
    }
}
