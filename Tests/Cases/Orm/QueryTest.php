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
 * @date 04/01/14.01.2014 03:04
 */

namespace Tests\Orm;

use Mindy\Tests\DatabaseTestCase;
use Tests\Models\Customer;
use Tests\Models\Group;
use Tests\Models\Membership;
use Tests\Models\User;

class QueryTest extends DatabaseTestCase
{
    public $prefix = '';

    public function setUp()
    {
        parent::setUp();

        $this->initModels([new User, new Group, new Membership, new Customer]);

        $group = new Group();
        $group->name = 'test';
        $group->save();

        $this->items = [
            ['username' => 'Anton', 'password' => 'VeryGoodPassWord'],
            ['username' => 'Max', 'password' => 'The6estP@$$w0rd'],
        ];
        $users = [];
        foreach($this->items as $item) {
            $tmp = new User($item);
            $tmp->save();
            $users[] = $tmp;
        }

        foreach($users as $user) {
            $group->users->link($user);

            Customer::objects()->getOrCreate(['address' => 'test', 'user' => $user]);
        }

        $model = new User();
        $this->prefix = $model->getConnection()->tablePrefix;
    }

    public function tearDown()
    {
        $this->dropModels([new User, new Group, new Membership, new Customer]);
    }

    public function testFind()
    {
        $qs = User::objects();
        $this->assertEquals(2, $qs->count());
        $this->assertEquals([
            [
                'id' => 1,
                'username' => 'Anton',
                'password' => 'VeryGoodPassWord'
            ],
            [
                'id' => 2,
                'username' => 'Max',
                'password' => 'The6estP@$$w0rd'
            ]
        ], $qs->asArray()->all());
    }

    public function testFindWhere()
    {
        $qs = User::objects();
        $this->assertEquals(1, $qs->filter(['username' => 'Max'])->count());
        $this->assertEquals([
            [
                'id' => 2,
                'username' => 'Max',
                'password' => 'The6estP@$$w0rd'
            ]
        ], $qs->asArray()->all());
    }

    public function testExclude()
    {
        $qs = User::objects()->filter(['username' => 'Anton'])->exclude(['username' => 'Max']);
        $this->assertEquals(1, $qs->count());
        $this->assertEquals("SELECT COUNT(*) FROM `tests_user` `tests_user_1` WHERE ((`tests_user_1`.`username`='Anton')) AND (NOT ((`tests_user_1`.`username`='Max')))", $qs->countSql());
    }

    public function testOrExclude()
    {
        $qs = User::objects()->exclude(['username' => 'Max'])->orExclude(['username' => 'Anton']);
        $this->assertEquals(2, $qs->count());
        $this->assertEquals("SELECT COUNT(*) FROM `tests_user` `tests_user_1` WHERE (NOT ((`tests_user_1`.`username`='Max'))) OR (NOT ((`tests_user_1`.`username`='Anton')))", $qs->countSql());
    }

    public function testExactQs()
    {
        $group = Group::objects()->filter(['pk' => 1])->get();
        $this->assertEquals(1, Group::objects()->count());
        $this->assertEquals(2, $group->users->count());

        $user = User::objects()->filter(['pk' => 1])->get();
        $customer = Customer::objects()->filter(['user' => $user])->get();
        $this->assertEquals(1, $customer->pk);
    }
}
