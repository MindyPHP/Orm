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
use Modules\Tests\Models\User;
use Tests\OrmDatabaseTestCase;

class Issue63Test extends OrmDatabaseTestCase
{
    public $driver = 'sqlite';

    protected function getModels()
    {
        return [new User, new Customer];
    }

    public function setUp()
    {
        parent::setUp();

        (new User([
            'username' => 'foo'
        ]))->save();

        (new User([
            'username' => 'bar'
        ]))->save();

        (new Customer([
            'address' => 'foo'
        ]))->save();

        (new Customer([
            'address' => 'bar'
        ]))->save();
    }

    public function testIssue63()
    {
        $this->assertEquals(2, User::objects()->count());
        foreach (User::objects()->all() as $i => $user) {
            $customer = Customer::objects()->get(['pk' => $i + 1]);
            $customer->user = $user;
            $customer->save(['user']);
        }
        $this->assertEquals(2, Customer::objects()->count());
        list($first, $last) = Customer::objects()->order(['pk'])->asArray()->all();
        $this->assertEquals([
            'id' => 1,
            'user_id' => 1,
            'address' => 'foo'
        ], $first);
        $this->assertEquals([
            'id' => 2,
            'user_id' => 2,
            'address' => 'bar'
        ], $last);
        $this->assertEquals(1, Customer::objects()->filter(['user__username' => 'foo'])->count());
        $this->assertEquals(1, Customer::objects()->filter(['user__username' => 'bar'])->count());
    }
}
