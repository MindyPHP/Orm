<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 *
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 18:58
 */

namespace Mindy\Orm\Tests\Databases\Pgsql;

use Mindy\Orm\Tests\QueryBuilder\LookupRelationTest;
use Mindy\Orm\Tests\Models\Customer;
use Mindy\Orm\Tests\Models\Group;
use Mindy\Orm\Tests\Models\User;

class PgsqlLookupRelationTest extends LookupRelationTest
{
    public $driver = 'pgsql';

    public function testSimpleLookup()
    {
        $user = new User(['username' => 'foo']);
        $this->assertTrue($user->save());

        $customer = new Customer(['address' => 'test', 'user' => $user]);
        $this->assertTrue($customer->save());

        $sql = Customer::objects()->filter(['user__username__startswith' => 't'])->allSql();
        $this->assertSql(
            'SELECT [[customer_1]].* 
FROM [[customer]] AS [[customer_1]] 
LEFT JOIN [[users]] AS [[users_1]] ON [[customer_1]].[[user_id]]=[[users_1]].[[id]] 
WHERE ([[users_1]].[[username]]::text LIKE @t%@)', $sql);
    }

    public function testManyLookupAnother()
    {
        $user = new User(['username' => 'foo']);
        $this->assertTrue($user->save());

        $customer = new Customer(['address' => 'test', 'user' => $user]);
        $this->assertTrue($customer->save());

        $group = new Group(['name' => 'example']);
        $this->assertTrue($group->save());
        $user->groups->link($group);

        $qs = User::objects()->filter(['addresses__address__contains' => 'test']);
        $sql = $qs->allSql();
        $this->assertSql(
            'SELECT [[users_1]].* 
FROM [[users]] AS [[users_1]] 
LEFT JOIN [[customer]] AS [[customer_1]] ON [[customer_1]].[[user_id]]=[[users_1]].[[id]]
WHERE ([[customer_1]].[[address]]::text LIKE @%test%@)', $sql);
        $this->assertEquals(1, $qs->count());
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
            'user__groups__pk' => '1',
        ]);
        $sql = $qs->allSql();
        $this->assertSql(
            'SELECT [[users_1]].* FROM [[users]] AS [[users_1]] 
LEFT JOIN [[customer]] AS [[customer_1]] ON [[customer_1]].[[user_id]]=[[users_1]].[[id]]
LEFT JOIN [[membership]] AS [[membership_1]] ON [[membership_1]].[[user_id]]=[[users_1]].[[id]]
LEFT JOIN [[group]] AS [[group_1]] ON [[group_1]].[[id]]=[[membership_1]].[[group_id]] 
WHERE (([[customer_1]].[[address]]::text LIKE @%test%@) AND ([[group_1]].[[id]]=@1@))', $sql);
        $this->assertEquals(1, $qs->count());
    }
}
