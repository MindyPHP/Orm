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
 * @date 04/01/14.01.2014 20:41
 */

namespace Tests\Orm;


use Tests\DatabaseTestCase;
use Tests\Models\User;
use Tests\Models\Group;
use Tests\Models\Membership;

class ManyToManyFieldViaTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new User, new Group, new Membership]);
    }

    public function tearDown()
    {
        $this->dropModels([new User, new Group, new Membership]);
    }

    public function testSimple()
    {
        $group = new Group();
        $group->name = 'Administrators';
        $this->assertNull($group->pk);
        $this->assertInstanceOf('\Mindy\Orm\ManyToManyManager', $group->users);
        $this->assertEquals(0, $group->users->count());
        $this->assertEquals([], $group->users->all());

        $this->assertTrue($group->save());
        $this->assertEquals(1, $group->pk);

        $user = new User();
        $user->username = 'Anton';
        $user->password = 'VeryGoodP@ssword';
        $this->assertTrue($user->save());

        $this->assertEquals(0, count($group->users->all()));


        $group->users->link($user);
        $this->assertEquals(1, count($group->users->all()));

        $new = Group::objects()->get(['id' => 1]);
        $this->assertEquals(1, count($new->users->all()));

        $memberships = Membership::objects()->filter(['group_id' => 1, 'user_id' => 1])->all();
        $this->assertEquals(1, count($memberships));
    }
}
