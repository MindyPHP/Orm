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

    public function setUp()
    {
        parent::setUp();
        $this->basePath = realpath(__DIR__ . '/../../protected');

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

    public function testMin()
    {
        $this->assertEquals(User::objects()->min('id'), 1);
        $this->assertEquals(User::objects()->filter(['addresses__address__startswith' => 'A'])->min('id'), 1);
        $this->assertEquals(User::objects()->filter(['addresses__address__startswith' => 'A'])->min('addresses__id'), 1);
    }

    public function testMax()
    {
        $this->assertEquals(User::objects()->max('id'), 2);
        $this->assertEquals(User::objects()->filter(['addresses__address__startswith' => 'A'])->max('id'), 1);
        $this->assertEquals(User::objects()->filter(['addresses__address__startswith' => 'A'])->max('addresses__id'), 2);
    }

    public function testAvg()
    {
        $this->assertEquals(User::objects()->average('id'), 1.5);
        $this->assertEquals(User::objects()->filter(['addresses__address__startswith' => 'A'])->average('id'), 1);
        $this->assertEquals(User::objects()->filter(['addresses__address__startswith' => 'A'])->average('addresses__id'), 1.5);
    }

    public function testSum()
    {
        $this->assertEquals(User::objects()->sum('id'), 3);
        $this->assertEquals(User::objects()->filter(['addresses__address__startswith' => 'A'])->sum('addresses__id'), 3);
    }
}
