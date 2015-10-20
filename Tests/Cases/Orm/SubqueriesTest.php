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

use Modules\Tests\Models\Customer;
use Modules\Tests\Models\Group;
use Modules\Tests\Models\Membership;
use Modules\Tests\Models\User;
use Tests\OrmDatabaseTestCase;

abstract class SubqueriesTest extends OrmDatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new User, new Group, new Membership, new Customer]);

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

    public function tearDown()
    {
        $this->dropModels([new User, new Group, new Membership, new Customer]);
    }

    public function testSubqueryIn()
    {
        $qs = Group::objects()->filter(['id' => 1]);
        $users = User::objects()->filter([
            'groups__pk__in' => $qs->select('id')
        ])->all();
        $this->assertEquals(count($users), 1);
        $this->assertEquals($users[0]->username, 'Max');
    }
}
