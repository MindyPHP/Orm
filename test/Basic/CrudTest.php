<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 26/07/16
 * Time: 10:38
 */

namespace Mindy\Orm\Tests\Basic;

use Mindy\Query\Schema\TableSchema;
use Modules\Tests\Models\Customer;
use Modules\Tests\Models\Solution;
use Modules\Tests\Models\User;
use Mindy\Orm\Tests\OrmDatabaseTestCase;

abstract class CrudTest extends OrmDatabaseTestCase
{
    public function getModels()
    {
        return [new User, new Solution, new Customer];
    }

    public function testLastInsertId()
    {
        $c = $this->getConnection();
        $tableSchema = $c->getTableSchema(User::tableName(), true);
        $sql = $c->getQueryBuilder()->insert(User::tableName(), ['username'], ['foo']);
        $rows = $c->createCommand($sql)->execute();
        $this->assertEquals(1, $rows);
        $this->assertEquals(1, $c->getLastInsertID($tableSchema->sequenceName));
    }

    public function testBrokenLastInsertId()
    {
        if ($this->driver != 'mysql') {
            $this->markTestSkipped('mysql specific test');
        }
        $c = $this->getConnection();
        $sql = $c->getQueryBuilder()->insert(User::tableName(), ['username'], ['foo']);
        $rows = $c->createCommand($sql)->execute();
        // Выполняется запрос после INSERT и lastInsertId возвращает 0
        $tableSchema = $c->getTableSchema(User::tableName(), true);
        $this->assertInstanceOf(TableSchema::class, $tableSchema);
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

        $find = User::objects()->filter(['id' => 1])->get();
        $this->assertEquals(1, $find->id);
        $this->assertEquals(1, $find->pk);
        $this->assertEquals('foo', $find->username);
        $this->assertEquals('bar', $find->password);

        $find = User::objects()->limit(1)->get();
        $this->assertEquals(1, $find->id);
        $this->assertEquals(1, $find->pk);
        $this->assertEquals('foo', $find->username);
        $this->assertEquals('bar', $find->password);
    }

    public function testSave()
    {
        $model = new User();
        $model->username = 'Anton';
        $model->password = 'VeryGoodP@ssword';
        $this->assertEquals(0, User::objects()->count());
        $this->assertTrue($model->getIsNewRecord());

        $this->assertTrue($model->isValid());
        $this->assertNull($model->pk);
        $this->assertEquals('Anton', $model->username);
        $this->assertEquals('VeryGoodP@ssword', $model->password);

        $saved = $model->save();
        $this->assertTrue($saved);
        $this->assertEquals(1, User::objects()->count());
        $this->assertFalse($model->getIsNewRecord());
        $this->assertEquals(1, $model->pk);
        $this->assertEquals('Anton', $model->username);
        $this->assertEquals('VeryGoodP@ssword', $model->password);
    }

    public function testSaveSelectedField()
    {
        $model = new User();
        $model->username = 'Anton';
        $model->password = 'VeryGoodP@ssword';
        $this->assertEquals(0, User::objects()->count());
        $this->assertTrue($model->getIsNewRecord());
        $this->assertTrue($model->isValid());
        $this->assertNull($model->pk);
        $this->assertEquals('Anton', $model->username);
        $this->assertEquals('VeryGoodP@ssword', $model->password);

        $saved = $model->save(['username']);
        $this->assertTrue($saved);
        $this->assertEquals(1, User::objects()->count());
        $this->assertFalse($model->getIsNewRecord());
        $this->assertEquals(1, $model->pk);
        $this->assertEquals('Anton', $model->username);
        $this->assertEquals('VeryGoodP@ssword', $model->password);

        $model = User::objects()->get();
        $this->assertNull($model->password);
    }

    public function testUpdateMore()
    {
        $foo = new User();
        $this->assertTrue($foo->getIsNewRecord());
        $foo->username = 'Anton';
        $foo->password = 'VeryGoodP@ssword';
        $this->assertTrue($foo->getIsNewRecord());
        $this->assertTrue($foo->save());
        $this->assertFalse($foo->getIsNewRecord());
        $this->assertEquals(1, $foo->pk);

        $bar = new User(['username' => 'Max', 'password' => 'VeryGoodP@ssword']);
        $this->assertTrue($bar->getIsNewRecord());
        $this->assertTrue($bar->save());
        $this->assertEquals('Max', $bar->username);
        $this->assertEquals('VeryGoodP@ssword', $bar->password);
        $this->assertFalse($bar->getIsNewRecord());

        $this->assertEquals(2, User::objects()->count());

        $tmpFind = User::objects()->get(['id' => 1]);
        $this->assertFalse($tmpFind->getIsNewRecord());
        $this->assertEquals('Anton', $tmpFind->username);
        $this->assertEquals('VeryGoodP@ssword', $tmpFind->password);

        $updated = User::objects()->filter(['id' => 1])->update(['username' => 'Unknown']);
        $foo = User::objects()->get(['pk' => 1]);
        $this->assertEquals('Unknown', $foo->username);
        $this->assertEquals('Max', $bar->username);
        $this->assertEquals(1, $updated);

        $this->assertEquals(2, User::objects()->count());
        // Max already has username `Unknown`
        $updated = User::objects()->filter(['id__gt' => 1])->update(['username' => 'Unknown']);
        $this->assertEquals(1, $updated);

        $this->assertEquals(2, User::objects()->count());
        $updated = User::objects()->filter(['id__gte' => 0])->update(['username' => '123']);
        $this->assertEquals(2, $updated);
    }

    public function testGetOrCreate()
    {
        list($model, $created) = User::objects()->getOrCreate(['username' => 'Max', 'password' => 'VeryGoodP@ssword']);
        $this->assertFalse($model->getIsNewRecord());
        $this->assertEquals('Max', $model->username);
        $this->assertEquals('VeryGoodP@ssword', $model->password);
        $this->assertEquals(1, $model->pk);

        $newUser = new User();
        $newUser->username = 'Anton';
        $newUser->password = 'qwe';
        $newUser->save();
        $this->assertFalse($newUser->getIsNewRecord());
        $this->assertEquals(2, $newUser->pk);

        list($queryUser, $created) = User::objects()->getOrCreate(['username' => 'Anton']);
        $this->assertEquals('Anton', $queryUser->username);
        $this->assertEquals('qwe', $queryUser->password);
        $this->assertEquals(2, $queryUser->pk);
    }

    public function testUpdateOrCreate()
    {
        list($model, $created) = User::objects()->getOrCreate(['username' => 'Max', 'password' => 'VeryGoodP@ssword']);
        $this->assertEquals(1, $model->pk);
        $this->assertEquals('Max', $model->username);

        $updatedModel = User::objects()->updateOrCreate(['username' => 'Max'], ['username' => 'Oleg']);
        $this->assertEquals(1, $updatedModel->pk);
        $this->assertEquals('Oleg', $updatedModel->username);

        $updatedModel = User::objects()->updateOrCreate(['username' => 'Vasya'], ['username' => 'Vasya']);
        $this->assertEquals(2, $updatedModel->pk);
        $this->assertEquals('Vasya', $updatedModel->username);
    }

    public function testDeleteMore()
    {
        list($model, $created) = User::objects()->getOrCreate(['username' => 'Max', 'password' => 'VeryGoodP@ssword']);
        $this->assertNotNull($model->pk);
        $this->assertEquals(1, User::objects()->count());
        $this->assertEquals(1, $model->delete());
        $this->assertEquals(0, User::objects()->count());

        list($model, $created) = User::objects()->getOrCreate(['username' => 'Max', 'password' => 'VeryGoodP@ssword']);
        $this->assertNotNull($model->pk);
        $this->assertEquals(1, User::objects()->count());
        $this->assertEquals(1, User::objects()->filter(['pk' => 2])->delete());
        $this->assertEquals(0, User::objects()->count());
    }

    public function testDeleteTwo()
    {
        list($modelOne, $created) = User::objects()->getOrCreate(['username' => 'Max', 'password' => 'VeryGoodP@ssword']);
        list($modelTwo, $created) = User::objects()->getOrCreate(['username' => 'Anton', 'password' => 'VeryGoodP@ssword']);

        $modelOne->delete();
        $this->assertEquals(1, User::objects()->count());
    }

    public function testDeleteQsTwo()
    {
        list($modelOne, $created) = User::objects()->getOrCreate(['username' => 'Max', 'password' => 'VeryGoodP@ssword']);
        list($modelTwo, $created) = User::objects()->getOrCreate(['username' => 'Anton', 'password' => 'VeryGoodP@ssword']);

        User::objects()->filter(['username' => 'Max'])->delete();
        $this->assertEquals(1, User::objects()->count());
    }

    public function testChangedValues()
    {
        $model = new User();
        $this->assertTrue($model->getIsNewRecord());

        $model->username = 'Anton';
        $model->password = 'VeryGoodP@ssword';
        $this->assertEquals($model->getDirtyAttributes(), [
            'username' => 'Anton',
            'password' => 'VeryGoodP@ssword'
        ]);

        $model->save();
        $this->assertEquals($model->getDirtyAttributes(), []);
        $this->assertFalse($model->getIsNewRecord());

        $model->username = 'Vasya';
        $model->username = 'Vasya';
        $this->assertEquals($model->getDirtyAttributes(), [
            'username' => 'Vasya'
        ]);

        $model->save();

        $finded = User::objects()->filter(['pk' => $model->pk])->get();
        $this->assertNotNull($finded);
        $this->assertEquals('Vasya', $finded->username);
        $this->assertEquals($finded->getDirtyAttributes(), []);

        $finded->username = 'Max';
        $this->assertEquals($finded->getDirtyAttributes(), [
            'username' => 'Max'
        ]);
    }

    /**
     * https://github.com/studio107/Mindy_Query/issues/11
     * Issue #11
     */
    public function testIssue11()
    {
        // Fix hhvm test
        date_default_timezone_set('UTC');

        $this->initModels([new Solution], $this->getConnection());
        list($modelOne, $created) = Solution::objects()->getOrCreate([
            'status' => 1,
            'name' => 'test',
            'court' => 'qwe',
            'question' => 'qwe',
            'result' => 'qwe',
            'content' => 'qwe',
        ]);
        $this->assertEquals(1, $modelOne->pk);
        $sql = Solution::objects()->filter(['id' => '1'])->updateSql(['status' => 2]);
        $this->assertSql("UPDATE [[solution]] SET [[status]]=2 WHERE ([[id]]='1')", $sql);
        $this->dropModels([new Solution], $this->getConnection());
    }

    // https://github.com/studio107/Mindy_Orm/issues/65
    public function testSetAttributesIssue65()
    {
        list($model, $created) = User::objects()->getOrCreate(['username' => 'Max', 'password' => 'VeryGoodP@ssword']);
        $this->assertFalse($model->getIsNewRecord());
        $this->assertEquals('Max', $model->username);
        $this->assertEquals('VeryGoodP@ssword', $model->password);
        $this->assertEquals(1, $model->pk);
        $model->setAttributes(['username' => 'foo']);
        $saved = $model->save(['username']);
        $this->assertTrue($saved);
        $this->assertEquals('foo', $model->username);

        // Test
        $user = User::objects()->get(['pk' => 1]);
        $this->assertNotNull($user);
        $this->assertEquals('foo', $user->username);
        $user->setAttributes(['username' => 'bar']);
        $this->assertEquals('bar', $user->username);
        $this->assertEquals(['username' => 'bar'], $user->getDirtyAttributes(['username']));
        $saved = $user->save(['username']);
        $this->assertEquals('bar', $user->username);
        $this->assertTrue($saved);

        $user->setAttributes(['password' => 1]);
        $this->assertEquals(['password' => 1], $user->getDirtyAttributes(['password']));
        $user->save(['password']);
        $this->assertEquals(1, $user->password);
        $this->assertTrue($saved);
    }


    public function testCreateMore()
    {
        $this->assertTrue((new User(['username' => 'foo']))->save());
        $user = User::objects()->get(['pk' => 1]);

        Customer::objects()->create([
            'user' => $user,
            'address' => 'Broadway'
        ]);

        Customer::objects()->create([
            'user_id' => $user->id,
            'address' => 'Broadway'
        ]);

        $address1 = Customer::objects()->get(['pk' => 1]);
        $address2 = Customer::objects()->get(['pk' => 1]);

        $this->assertEquals($user->id, $address1->user->id);
        $this->assertEquals($user->id, $address2->user_id);
    }
}