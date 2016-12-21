<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 10:24.
 */

namespace Mindy\Orm\Tests;

use Mindy\Orm\Tests\Models\User;

class IsNewTest extends OrmDatabaseTestCase
{
    public $driver = 'mysql';

    public function getModels()
    {
        return [new User()];
    }

    public function testSimple()
    {
        $user = new User();
        $this->assertTrue($user->getIsNewRecord());

        $user->pk = 1;
        $this->assertTrue($user->getIsNewRecord());

        $user->username = 'foo';
        $user->password = 'bar';
        $this->assertTrue($user->save());
        $this->assertFalse($user->getIsNewRecord());

        $user->username = 'example';
        $this->assertFalse($user->getIsNewRecord());

        $user->pk = 2;
        $this->assertTrue($user->getIsNewRecord());

        list($user, $created) = User::objects()->getOrCreate(['username' => 'foo', 'password' => 'bar']);
        $this->assertFalse($created);
        $this->assertFalse($user->getIsNewRecord());

        /** @var $newUser User */
        list($newUser, $created) = User::objects()->getOrCreate(['username' => 'foo123', 'password' => 'bar']);
        $this->assertTrue($created);
        $this->assertFalse($newUser->getIsNewRecord());

        $model = User::objects()->get(['username' => 'foo123']);
        $this->assertFalse($model->getIsNewRecord());

        /** @var $updatedUser User */
        $updatedUser = User::objects()->updateOrCreate(['username' => 'foo123'], ['username' => 'john']);
        $this->assertFalse($updatedUser->getIsNewRecord());
        $this->assertEquals('john', $updatedUser->username);

        $updatedUser = User::objects()->updateOrCreate(['username' => 'unknown'], ['username' => 'mike']);
        $this->assertFalse($updatedUser->getIsNewRecord());
        $this->assertEquals('mike', $updatedUser->username);
    }
}
