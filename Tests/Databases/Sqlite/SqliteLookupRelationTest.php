<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Databases\Sqlite;

use Mindy\Orm\Tests\Models\Customer;
use Mindy\Orm\Tests\Models\Group;
use Mindy\Orm\Tests\Models\User;
use Mindy\Orm\Tests\QueryBuilder\LookupRelationTest;

class SqliteLookupRelationTest extends LookupRelationTest
{
    public $driver = 'sqlite';

    public function testManyLookup()
    {
        $user = new User(['username' => 'foo']);
        $this->assertTrue($user->save());

        $customer = new Customer(['address' => 'test', 'user' => $user]);
        $this->assertTrue($customer->save());

        $group = new Group(['name' => 'example']);
        $this->assertTrue($group->save());
        $user->groups->link($group);

        $qs = Customer::objects()->filter(['user__groups__name' => 'example']);
        $sql = $qs->allSql();
        $this->assertSame('SELECT customer_1.* FROM customer AS customer_1 LEFT JOIN users AS users_1 ON customer_1.user_id=users_1.id LEFT JOIN membership AS membership_1 ON membership_1.user_id=users_1.id LEFT JOIN "group" AS group_1 ON group_1.id=membership_1.group_id WHERE (group_1.name = \'example\')', $sql);
        $this->assertEquals(1, $qs->count());
        $this->assertEquals(['id' => '1', 'user_id' => '1', 'address' => 'test'], $qs->asArray()->all()[0]);
    }

    public function testManyLookupMultiple()
    {
        $user = new User(['username' => 'foo']);
        $this->assertTrue($user->save());

        $customer = new Customer(['address' => 'test', 'user' => $user]);
        $this->assertTrue($customer->save());

        $group = new Group(['name' => 'example']);
        $this->assertTrue($group->save());
        $user->groups->link($group);

        $qs = User::objects()->filter([
            'addresses__address__contains' => 'test',
            'groups__pk' => '1',
        ]);
        $sql = $qs->allSql();
        $this->assertSame('SELECT users_1.* FROM users AS users_1 LEFT JOIN customer AS customer_1 ON customer_1.user_id=users_1.id LEFT JOIN membership AS membership_1 ON membership_1.user_id=users_1.id LEFT JOIN "group" AS group_1 ON group_1.id=membership_1.group_id WHERE ((customer_1.address LIKE \'%test%\') AND (group_1.id = \'1\'))', $sql);
        $this->assertEquals(1, $qs->count());
    }
}
