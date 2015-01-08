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

use Mindy\Tests\DatabaseTestCase;
use Tests\Models\Customer;
use Tests\Models\Group;
use Tests\Models\Membership;
use Tests\Models\User;

class ValuesListTest extends DatabaseTestCase
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
        $group_prog->users->link($max);

        $max_home = new Customer();
        $max_home->address = "Max home";
        $max_home->user = $max;
        $max_home->save();
    }

    public function tearDown()
    {
        $this->dropModels([new User, new Group, new Membership, new Customer]);
    }

    public function testValuesList()
    {
        $values = Customer::objects()->valuesList(['address', 'user__username']);

        $this->assertEquals([
            ['address' => 'Anton home', 'user__username' => 'Anton'],
            ['address' => "Anton work", 'user__username' => 'Anton'],
            ['address' => "Max home", 'user__username' => 'Max'],
        ], $values);
    }

    public function testValuesListFlat()
    {
        $values = Customer::objects()->valuesList(['address', 'user__username']);
        $this->assertEquals([
            ['address' => 'Anton home', 'user__username' => 'Anton'],
            ['address' => "Anton work", 'user__username' => 'Anton'],
            ['address' => "Max home", 'user__username' => 'Max'],
        ], $values);

        $values = Customer::objects()->valuesList(['user__username'], true);
        $this->assertEquals([
            'Anton',
            'Anton',
            'Max',
        ], $values);
    }
}
