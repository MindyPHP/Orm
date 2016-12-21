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
 * @date 04/01/14.01.2014 20:41
 */

namespace Mindy\Orm\Tests\QueryBuilder;

use Mindy\Orm\Tests\Models\Customer;
use Mindy\Orm\Tests\Models\Group;
use Mindy\Orm\Tests\Models\Membership;
use Mindy\Orm\Tests\Models\User;
use Mindy\Orm\Tests\OrmDatabaseTestCase;

abstract class LookupRelationTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new User(), new Group(), new Membership(), new Customer()];
    }

    public function testSimpleExact()
    {
        $user = new User(['username' => 'foo']);
        $this->assertTrue($user->save());

        $customer = new Customer(['address' => 'test', 'user' => $user]);
        $this->assertTrue($customer->save());

        $sql = Customer::objects()->filter(['user__username' => 'foo'])->allSql();
        $this->assertSql(
            'SELECT [[customer_1]].* 
FROM [[customer]] AS [[customer_1]] 
LEFT JOIN [[users]] AS [[users_1]] ON [[customer_1]].[[user_id]]=[[users_1]].[[id]] 
WHERE ([[users_1]].[[username]]=@foo@)', $sql);
    }

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
WHERE ([[users_1]].[[username]] LIKE @t%@)', $sql);
    }

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
        $this->assertSql(
            'SELECT [[customer_1]].* 
FROM [[customer]] AS [[customer_1]] 
LEFT JOIN [[users]] AS [[users_1]] ON [[customer_1]].[[user_id]]=[[users_1]].[[id]]
LEFT JOIN [[membership]] AS [[membership_1]] ON [[membership_1]].[[user_id]]=[[users_1]].[[id]]
LEFT JOIN [[group]] AS [[group_1]] ON [[group_1]].[[id]]=[[membership_1]].[[group_id]]
WHERE ([[group_1]].[[name]]=@example@)', $sql);
        $this->assertEquals(1, $qs->count());
        $this->assertEquals(['id' => '1', 'user_id' => '1', 'address' => 'test'], $qs->asArray()->all()[0]);
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
WHERE ([[customer_1]].[[address]] LIKE @%test%@)', $sql);
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
            'groups__pk' => '1',
        ]);
        $sql = $qs->allSql();
        $this->assertSql(
            'SELECT [[users_1]].* FROM [[users]] AS [[users_1]]
LEFT JOIN [[customer]] AS [[customer_1]] ON [[customer_1]].[[user_id]]=[[users_1]].[[id]]
LEFT JOIN [[membership]] AS [[membership_1]] ON [[membership_1]].[[user_id]]=[[users_1]].[[id]]
LEFT JOIN [[group]] AS [[group_1]] ON [[group_1]].[[id]]=[[membership_1]].[[group_id]]
WHERE (([[customer_1]].[[address]] LIKE @%test%@) AND ([[group_1]].[[id]]=@1@))', $sql);
        $this->assertEquals(1, $qs->count());
    }

    public function testLookupsClean1()
    {
        $user = new User(['username' => 'foo']);
        $this->assertTrue($user->save());

        $customer = new Customer(['address' => 'test', 'user' => $user]);
        $this->assertTrue($customer->save());

        $qs = User::objects()->filter(['addresses__address__contains' => 'es']);
        $this->assertEquals(1, $qs->count());
        $this->assertEquals(1, count($qs->all()));
    }
}
