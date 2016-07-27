<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 27/07/16
 * Time: 13:56
 */

namespace Mindy\Orm\Tests\Basic;

use Modules\Tests\Models\User;
use Mindy\Orm\Tests\OrmDatabaseTestCase;

class DirtyAttributesTest extends OrmDatabaseTestCase
{
    public $driver = 'mysql';

    protected function getModels()
    {
        return [new User];
    }

    public function testDirtyAttributes()
    {
        $user = new User();
        $user->username = '123';
        $user->password = '123';
        $this->assertEquals([
            'username' => '123',
            'password' => '123'
        ], $user->getDirtyAttributes());

        $user->username = '321';
        $user->password = '321';
        $this->assertTrue($user->save());
        $this->assertEquals([
            'username' => '321',
            'password' => '321',
            'id' => '1'
        ], $user->getOldAttributes());
    }
}