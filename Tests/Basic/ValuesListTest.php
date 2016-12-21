<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 27/07/16
 * Time: 16:43.
 */

namespace Mindy\Orm\Tests\Basic;

use Mindy\Orm\Tests\OrmDatabaseTestCase;
use Mindy\Orm\Tests\Models\Customer;
use Mindy\Orm\Tests\Models\Group;
use Mindy\Orm\Tests\Models\User;

abstract class ValuesListTest extends OrmDatabaseTestCase
{
    public function getModels()
    {
        return [new Group(), new User(), new Customer()];
    }

    public function testValuesList()
    {
        $group = new Group();
        $group->name = 'Administrators';
        $group->save();

        $groupProg = new Group();
        $groupProg->name = 'Programmers';
        $groupProg->save();

        $anton = new User();
        $anton->username = 'Anton';
        $anton->password = 'Passwords';
        $anton->save();

        $groupProg->users->link($anton);

        $anton_home = new Customer();
        $anton_home->address = 'Anton home';
        $anton_home->user = $anton;
        $anton_home->save();

        $anton_work = new Customer();
        $anton_work->address = 'Anton work';
        $anton_work->user = $anton;
        $anton_work->save();

        $max = new User();
        $max->username = 'Max';
        $max->password = 'MaxPassword';
        $max->save();

        $group->users->link($max);

        $max_home = new Customer();
        $max_home->address = 'Max home';
        $max_home->user = $max;
        $max_home->save();

        $values = Customer::objects()->valuesList(['address', 'user__username']);
        $this->assertEquals([
            ['address' => 'Anton home', 'user__username' => 'Anton'],
            ['address' => 'Anton work', 'user__username' => 'Anton'],
            ['address' => 'Max home', 'user__username' => 'Max'],
        ], $values);

        $this->assertEquals([
            ['address' => 'Anton home', 'user__username' => 'Anton'],
            ['address' => 'Anton work', 'user__username' => 'Anton'],
            ['address' => 'Max home', 'user__username' => 'Max'],
        ], Customer::objects()->valuesList(['address', 'user__username']));
        $this->assertEquals([
            'Anton',
            'Anton',
            'Max',
        ], Customer::objects()->valuesList(['user__username'], true));
    }
}
