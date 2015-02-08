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

namespace Tests\Orm;

use Tests\Models\Group;
use Tests\Models\Customer;
use Tests\Models\User;
use Tests\Models\Membership;
use Tests\OrmDatabaseTestCase;

abstract class OrderByLookupTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new User, new Group, new Membership, new Customer];
    }

    public function setUp()
    {
        parent::setUp();

        $group = new Group();
        $group->name = 'Administrators';
        $group->save();

        $group_prog = new Group();
        $group_prog->name = 'Programmers';
        $group_prog->save();

        $anton = new User();
        $anton->username = 'Anton';
        $anton->password = 'Passwords';
        $anton->save();

        $group_prog->users->link($anton);

        $anton_home = new Customer();
        $anton_home->address = "Anton home";
        $anton_home->user = $anton;
        $anton_home->save();

        $anton_work = new Customer();
        $anton_work->address = "Anton work";
        $anton_work->user = $anton;
        $anton_work->save();

        $max = new User();
        $max->username = 'Max';
        $max->password = 'MaxPassword';
        $max->save();

        $group->users->link($max);

        $max_home = new Customer();
        $max_home->address = "Max home";
        $max_home->user = $max;
        $max_home->save();
    }

    public function testWithoutLookup()
    {
        $users = User::objects()->order(['-username'])->all();
        $this->assertEquals(count($users), 2);
        $this->assertEquals($users[0]->username, 'Max');
        $this->assertEquals($users[1]->username, 'Anton');

        $users = User::objects()->order(['username'])->all();
        $this->assertEquals(count($users), 2);
        $this->assertEquals($users[0]->username, 'Anton');
        $this->assertEquals($users[1]->username, 'Max');
    }

    public function testForeignLookup()
    {
        $addresses = Customer::objects()->order(['-user__username', '-address'])->all();

        $this->assertEquals(count($addresses), 3);
        $this->assertEquals($addresses[0]->address, 'Max home');
        $this->assertEquals($addresses[1]->address, 'Anton work');
        $this->assertEquals($addresses[2]->address, 'Anton home');

        $addresses = Customer::objects()->order(['user__username', 'address'])->all();
        $this->assertEquals(count($addresses), 3);
        $this->assertEquals($addresses[0]->address, 'Anton home');
        $this->assertEquals($addresses[1]->address, 'Anton work');
        $this->assertEquals($addresses[2]->address, 'Max home');
    }

    public function testHasManyLookup()
    {
//        $this->assertEquals(2, User::objects()->count());
//        $users = User::objects()->order(['-addresses__address'])->group(false)->all();
//        $this->assertEquals(count($users), 3);
//        $this->assertEquals($users[0]->username, 'Max');
//        $this->assertEquals($users[1]->username, 'Anton');
//        $this->assertEquals($users[2]->username, 'Anton');
//
//        $addresses = User::objects()->order(['addresses__address'])->group(false)->all();
//        $this->assertEquals(count($users), 3);
//        $this->assertEquals($addresses[0]->username, 'Anton');
//        $this->assertEquals($addresses[1]->username, 'Anton');
//        $this->assertEquals($addresses[2]->username, 'Max');

//        $usersSql = User::objects()->order(['-addresses__address'])->distinct()->allSql();
//        $this->assertEquals(ltrim(str_replace("\n", ' ', '
//SELECT *
//FROM (SELECT DISTINCT ON ("tests_user_1"."id") "tests_user_1"."id",  "tests_user_1".*, "tests_customer_2"."address"
//FROM "tests_user" "tests_user_1"
//LEFT OUTER JOIN "tests_customer" "tests_customer_2" ON "tests_user_1"."id" = "tests_customer_2"."user_id"
//GROUP BY "tests_customer_2"."address", "tests_user_1"."id", "tests_user_1"."username", "tests_user_1"."password"
//ORDER BY "tests_user_1"."id", "tests_customer_2"."address" DESC) "_tmp"
//ORDER BY "_tmp"."address" DESC')), $usersSql);

        $users = User::objects()->order(['-addresses__address'])->distinct()->all();
        $this->assertEquals(2, count($users));
        $this->assertEquals($users[0]->username, 'Max');
        $this->assertEquals($users[1]->username, 'Anton');

        $addresses = User::objects()->order(['addresses__address'])->distinct()->all();
        $this->assertEquals(2, count($users));
        $this->assertEquals($addresses[0]->username, 'Anton');
        $this->assertEquals($addresses[1]->username, 'Max');
    }

    public function testManyToManyLookup()
    {
        $users = User::objects()->order(['-groups__name'])->all();

        $this->assertEquals(count($users), 2);
        $this->assertEquals($users[0]->username, 'Anton');
        $this->assertEquals($users[1]->username, 'Max');

        $addresses = User::objects()->order(['groups__name'])->all();
        $this->assertEquals(count($users), 2);
        $this->assertEquals($addresses[0]->username, 'Max');
        $this->assertEquals($addresses[1]->username, 'Anton');
    }
}
