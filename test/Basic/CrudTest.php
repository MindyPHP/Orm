<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 26/07/16
 * Time: 10:38
 */

namespace Mindy\Orm\Tests\Basic;

use Modules\Tests\Models\User;
use Tests\OrmDatabaseTestCase;

class CrudTest extends OrmDatabaseTestCase
{
    public $driver = 'mysql';

    public function getModels()
    {
        return [new User];
    }

    public function testLastInsertId()
    {
        $c = $this->getConnection();
        $sql = $c->getQueryBuilder()->insert(User::tableName(), ['username'], ['foo']);
        $rows = $c->createCommand($sql)->execute();
        $this->assertEquals(1, $rows);
        $this->assertEquals(1, $c->getLastInsertID());
    }

    public function testBrokenLastInsertId()
    {
        $c = $this->getConnection();
        $sql = $c->getQueryBuilder()->insert(User::tableName(), ['username'], ['foo']);
        $rows = $c->createCommand($sql)->execute();
        // Выполняется запрос после INSERT и lastInsertId возвращает 0
        $tableSchema = $c->getTableSchema(User::tableName(), true);
        $this->assertEquals(1, $rows);
        $this->assertEquals(0, $c->getLastInsertID($tableSchema->sequenceName));
    }

    public function testCreate()
    {
        $user = new User();
        $user->username = 'foo';
        $user->password = 'bar';
        $this->assertTrue($user->save());

        $this->assertEquals(1, $user->id);
        $this->assertEquals(1, $user->pk);
        $this->assertEquals('foo', $user->username);
        $this->assertEquals('bar', $user->password);
        $this->assertEquals(1, User::objects()->count());

        $user = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user->save());

        $this->assertEquals(2, $user->id);
        $this->assertEquals(2, $user->pk);
        $this->assertEquals('foo', $user->username);
        $this->assertEquals('bar', $user->password);
        $this->assertEquals(2, User::objects()->count());
    }

    public function testUpdate()
    {
        $user = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user->save());
        $this->assertEquals(1, User::objects()->count());

        $user->username = 'qwerty';
        $this->assertTrue($user->save());
        $this->assertEquals(1, $user->id);
        $this->assertEquals(1, $user->pk);
        $this->assertEquals('qwerty', $user->username);
    }

    public function testDelete()
    {
        $user = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user->save());
        $this->assertEquals(1, User::objects()->count());

        $user->delete();
        $this->assertEquals(0, User::objects()->count());
    }

    public function testRead()
    {
        $user = new User(['username' => 'foo', 'password' => 'bar']);
        $this->assertTrue($user->save());

        $find = User::objects()->get(['id' => 1]);
        $this->assertEquals(1, $find->id);
        $this->assertEquals(1, $find->pk);
        $this->assertEquals('foo', $find->username);
        $this->assertEquals('bar', $find->password);
    }
}