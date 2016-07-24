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

use Modules\Tests\Models\Customer;
use Modules\Tests\Models\ModelTyre;
use Modules\Tests\Models\Tyre;
use Modules\Tests\Models\User;
use Tests\OrmDatabaseTestCase;

class Issue25Test extends OrmDatabaseTestCase
{
    public $driver = 'mysql';

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
        User::objects()->filter(['addresses__address' => 'address'])->delete();
        $this->assertEquals(1, User::objects()->count());
    }
}
