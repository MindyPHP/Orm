<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 14/02/15 15:15
 */

namespace Tests\Cases\Orm\Issues;

use Tests\Models\Customer;
use Tests\Models\ModelTyre;
use Tests\Models\Tyre;
use Tests\Models\User;
use Tests\OrmDatabaseTestCase;

class Issue25Test extends OrmDatabaseTestCase
{
    public $driver = 'sqlite';

    protected function getModels()
    {
        return [new User, new Customer, new Tyre, new ModelTyre];
    }

    public function setUp()
    {
        parent::setUp();

        $user = new User([
            'username' => 'foo'
        ]);
        $user->save();

        (new User([
            'username' => 'bar'
        ]))->save();

        (new Customer([
            'user' => $user,
            'address' => 'address'
        ]))->save();
    }

    public function testIssue25()
    {
        $this->assertEquals(2, User::objects()->count());
        $qs = User::objects()->filter(['addresses__address' => 'address']);
        $this->assertEquals(1, $qs->count());
        $this->setExpectedException('\Mindy\Exception\Exception', "You can't use relationship in filter when run delete query");
        User::objects()->filter(['addresses__address' => 'address'])->delete();
    }
}
