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

use Modules\Tests\Models\Customer;
use Modules\Tests\Models\User;
use Tests\OrmDatabaseTestCase;

class Issue19Test extends OrmDatabaseTestCase
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

    public function testIssue19()
    {
        $this->assertEquals('SELECT `tests_user_1`.* FROM `tests_user` `tests_user_1` WHERE (`tests_user_1`.`id`=6) LIMIT 1', User::objects()->limit(1)->getSql(['id' => 6]));
        $this->assertEquals('SELECT `tests_user_1`.* FROM `tests_user` `tests_user_1` WHERE ((`tests_user_1`.`id`=6)) LIMIT 1', User::objects()->filter(['id' => 6])->limit(1)->getSql());
    }
}
