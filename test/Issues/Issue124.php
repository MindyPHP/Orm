<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 14/02/15 16:01
 */

namespace Tests\Cases\Orm\Issues;

use Mindy\Orm\Q\OrQ;
use Modules\Tests\Models\Customer;
use Modules\Tests\Models\User;
use Tests\OrmDatabaseTestCase;

class Issue124Test extends OrmDatabaseTestCase
{
    public $driver = 'sqlite';

    protected function getModels()
    {
        return [new User, new Customer];
    }

    public function setUp()
    {
        parent::setUp();

        $user = new User(['username' => 'bar']);
        $user->save();

        (new Customer([
            'user' => $user,
            'address' => 'address'
        ]))->save();
    }

    public function testIssue124()
    {
        $user = User::objects()->get();
        $this->assertEquals("SELECT `tests_customer_1`.* FROM `tests_customer` `tests_customer_1` WHERE ((((`tests_customer_1`.`user_id` IS NULL)) OR ((`tests_customer_1`.`user_id`='1'))))",
            Customer::objects()->filter([
                new OrQ([
                    ['user__isnull' => true],
                    ['user' => $user]
                ])
            ])->getSql());
    }
}
