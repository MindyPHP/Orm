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

use Mindy\Query\ConnectionManager;
use Tests\Models\Customer;
use Tests\Models\Group;
use Tests\Models\Membership;
use Tests\Models\User;
use Tests\OrmDatabaseTestCase;

abstract class QueryTest extends OrmDatabaseTestCase
{
    public $prefix = '';

    protected function getModels()
    {
        return [new User, new Group, new Membership, new Customer];
    }

    public function setUp()
    {
        parent::setUp();

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
        $this->prefix = $model->getDb()->tablePrefix;
    }

    public function testFind()
    {
        $qs = User::objects();
        $this->assertEquals(2, $qs->count());
        $this->assertEquals([[
            'id' => 1,
            'username' => 'Anton',
            'password' => 'VeryGoodPassWord'
        ], [
            'id' => 2,
            'username' => 'Max',
            'password' => 'The6estP@$$w0rd'
        ]], $qs->asArray()->all());
    }

    public function testFindWhere()
    {
        $qs = User::objects();
        $this->assertEquals(1, $qs->filter(['username' => 'Max'])->count());
        $this->assertEquals([[
            'id' => 2,
            'username' => 'Max',
            'password' => 'The6estP@$$w0rd'
        ]], $qs->asArray()->all());
    }

    public function testExclude()
    {
        $db = ConnectionManager::getDb();
        $tableSql = $db->schema->quoteColumnName('tests_user');
        $tableAliasSql = $db->schema->quoteColumnName('tests_user_1');
        $usernameSql = $db->schema->quoteColumnName('username');

        $qs = User::objects()->filter(['username' => 'Anton'])->exclude(['username' => 'Max']);
        $this->assertEquals(1, $qs->count());
        $this->assertEquals("SELECT COUNT(*) FROM $tableSql $tableAliasSql WHERE (($tableAliasSql.$usernameSql='Anton')) AND (NOT (($tableAliasSql.$usernameSql='Max')))", $qs->countSql());
    }

    public function testOrExclude()
    {
        $qs = User::objects()->exclude(['username' => 'Max'])->orExclude(['username' => 'Anton']);
        $this->assertEquals(2, $qs->count());

        $db = ConnectionManager::getDb();
        $tableSql = $db->schema->quoteColumnName('tests_user');
        $tableAliasSql = $db->schema->quoteColumnName('tests_user_1');
        $usernameSql = $db->schema->quoteColumnName('username');

        $this->assertEquals("SELECT COUNT(*) FROM $tableSql $tableAliasSql WHERE (NOT (($tableAliasSql.$usernameSql='Max'))) OR (NOT (($tableAliasSql.$usernameSql='Anton')))", $qs->countSql());
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
