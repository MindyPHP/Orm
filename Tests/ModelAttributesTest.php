<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests;

use Mindy\Orm\Tests\Models\User;

class ModelAttributesTest extends OrmDatabaseTestCase
{
    public $driver = 'sqlite';

    protected function getModels()
    {
        return [new User()];
    }

    public function testDirtyAttributes()
    {
        $user = new User();
        $user->username = '123';
        $user->password = '123';
        $this->assertEquals(['username', 'password'], $user->getDirtyAttributes());
        $this->assertEquals(['username' => null, 'password' => null], $user->getOldAttributes());

        $user->username = '321';
        $user->password = '321';
        $this->assertTrue($user->save());
        $this->assertEquals([], $user->getOldAttributes());
    }

    public function testOldAttributes()
    {
        $user = new User();

        $user->username = 'foo';
        $this->assertNull($user->getOldAttribute('username'));

        $user->username = 'bar';
        $this->assertEquals('foo', $user->getOldAttribute('username'));

        $this->assertTrue($user->save());

        $this->assertEquals('bar', $user->username);
        $this->assertNull($user->getOldAttribute('username'));
    }
}
