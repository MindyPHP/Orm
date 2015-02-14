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
use Tests\Models\User;
use Tests\OrmDatabaseTestCase;

class Issue20Test extends OrmDatabaseTestCase
{
    public $driver = 'sqlite';

    protected function getModels()
    {
        return [new User, new Customer];
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

    public function testIssue20()
    {
        $qs = User::objects()->with(['addresses'])->asArray();
        $this->assertEquals([
            [
                'customer' => [
                    'id' => 1,
                    'user_id' => 1,
                    'address' => 'address'
                ],
                'id' => 1,
                'username' => 'foo',
                'password' => '',
                'user_id' => 1
            ],
            [
                'customer' => [
                    'id' => null,
                    'user_id' => null,
                    'address' => ''
                ],
                'id' => 2,
                'username' => 'bar',
                'password' => '',
                'user_id' => ''
            ]
        ], $qs->all());

        $qs = User::objects()->filter(['addresses__address' => 'address'])->with(['addresses'])->asArray();
        $this->assertEquals([
            [
                'customer' => [
                    'id' => 1,
                    'user_id' => 1,
                    'address' => 'address'
                ],
                'id' => 1,
                'username' => 'foo',
                'password' => '',
                'user_id' => 1
            ]
        ], $qs->all());
    }
}
