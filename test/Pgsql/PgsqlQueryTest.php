<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 19:09
 */

namespace Tests\Cases\Orm\Pgsql;

use Modules\Tests\Models\User;
use Mindy\Orm\Tests\QueryTest;

class PgsqlQueryTest extends QueryTest
{
    public $driver = 'pgsql';

    public function testGet()
    {
        $user = User::objects()->get(['pk' => 1]);

        $this->assertEquals('Anton', $user->username);

        $this->assertEquals('SELECT "user_1".* FROM "user" "user_1" WHERE ("user_1"."id"=1)', User::objects()->asArray()->getSql(['pk' => 1]));

        $this->assertEquals([
            'id' => 1,
            'username' => 'Anton',
            'password' => 'VeryGoodPassWord'
        ], User::objects()->asArray()->get(['pk' => 1]));
    }
}
