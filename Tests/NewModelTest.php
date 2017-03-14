<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests;

use Doctrine\DBAL\Driver\Connection;
use Mindy\Orm\AbstractModel;
use Mindy\Orm\Fields\AutoField;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Tests\Models\CompositeModel;
use Mindy\Orm\Tests\Models\CustomPrimaryKeyModel;
use Mindy\Orm\Tests\Models\DefaultPrimaryKeyModel;
use Mindy\Orm\Tests\Models\DummyModel;
use Mindy\Orm\Tests\Models\NewModel;
use Mindy\Orm\Tests\Models\User;

class NewModelTest extends OrmDatabaseTestCase
{
    public function testInit()
    {
        $model = new DummyModel();
        $this->assertTrue($model->getIsNewRecord());
        $this->assertEquals([], $model->getFields());
        $this->assertEquals(['id' => null], $model->getAttributes());
        $this->assertEquals(['id'], $model->getMeta()->getAttributes());
        $this->assertEquals([], $model->getOldAttributes());
    }

    public function testCompositeKey()
    {
        $model = new CompositeModel(['user_id' => 1]);
        $this->assertEquals(['order_id', 'user_id'], $model->getPrimaryKeyName(true));
        $this->assertEquals(['order_id' => null, 'user_id' => 1], $model->getPrimaryKeyValues());
    }

    public function testSetGet()
    {
        $model = new NewModel(['username' => 'foo', 'password' => 'bar']);

        $this->assertFalse(property_exists($model, 'username'));
        $this->assertFalse(property_exists($model, 'password'));
        $this->assertFalse(property_exists($model, 'id'));
        $this->assertFalse(property_exists($model, 'pk'));

        $this->assertTrue(isset($model['username']));
        $this->assertTrue(isset($model['password']));
        $this->assertTrue(isset($model['id']));
        $this->assertTrue(isset($model['pk']));

        $this->assertEquals('foo', $model->username);
        $this->assertEquals('bar', $model->password);

        $model->username = 'mike';
        $this->assertEquals('mike', $model->username);

        $model->id = 1;
        $this->assertEquals(1, $model->pk);

        $model->id = '1';
        $this->assertEquals('1', $model->pk);

        $model->pk = 2;
        $this->assertEquals(2, $model->id);

        unset($model->pk);
        $this->assertNull($model->pk);
    }

    public function testPrimaryKey()
    {
        $custom = new CustomPrimaryKeyModel();
        $this->assertInstanceOf(IntField::class, $custom->getField('pk'));

        $custom = new DefaultPrimaryKeyModel();
        $this->assertInstanceOf(AutoField::class, $custom->getField('pk'));

        $custom->pk = 1;
        $this->assertSame(1, $custom->pk);
        $this->assertFalse(empty($custom->pk));
    }

    public function testGetHasField()
    {
        $model = new NewModel();
        $this->assertTrue($model->hasField('username'));
        $this->assertFalse($model->hasField('unknown'));
        $this->assertInstanceOf(CharField::class, $model->getField('username'));
    }

    public function testAttributes()
    {
        $model = new NewModel();

        $this->assertEquals(['id' => null, 'username' => null, 'password' => null], $model->getAttributes());
        $this->assertEquals(3, count($model->getMeta()->getAttributes()));

        $model->username = 'foo';
        $this->assertNull($model->getOldAttribute('username'));

        $model->username = 'bar';
        $this->assertEquals('foo', $model->getOldAttribute('username'));

        $this->assertTrue($model->save());

        $this->assertEquals('bar', $model->username);
        $this->assertNull($model->getOldAttribute('username'));
    }

    public function testSelectedAttributes()
    {
        $this->initModels([new User()], $this->getConnection());

        $model = new User(['username' => 'foo']);
        $this->assertTrue($model->save());
        $this->assertEquals(1, User::objects()->count());

        $model->username = 'bar';
        $model->password = 'example';
        $this->assertTrue($model->save(['password']));

        $this->assertSame('bar', $model->username);
        $this->assertSame('example', $model->password);

        /** @var \Mindy\Orm\Model $model */
        $model = User::objects()->get(['password' => 'example']);
        $this->assertSame('foo', $model->username);

        $this->dropModels([new User()], $this->getConnection());
    }

    public function testDirtyAttributes()
    {
        $user = new NewModel();
        $user->username = '123';
        $user->password = '123';
        $this->assertEquals(['username', 'password'], $user->getDirtyAttributes());
        $this->assertEquals(['username' => null, 'password' => null], $user->getOldAttributes());

        $user->username = '321';
        $user->password = '321';
        $this->assertTrue($user->save());
        $this->assertEquals([], $user->getOldAttributes());
    }

    public function testArrayAccess()
    {
        $model = new NewModel(['username' => 'foo', 'password' => 'bar']);
        $this->assertSame('foo', $model['username']);
        $this->assertSame('bar', $model['password']);
        unset($model['username']);
        $this->assertNull($model['username']);
        $model['username'] = 'mike';
        $this->assertSame('mike', $model['username']);
    }

    public function testTableName()
    {
        $this->assertSame('abstract_model', AbstractModel::tableName());
        $this->assertSame('new_model', NewModel::tableName());
        $this->assertSame('composite_model', CompositeModel::tableName());
    }

    public function testConnection()
    {
        $this->assertInstanceOf(Connection::class, (new User())->getConnection());
    }

    public function testChangedAttributes()
    {
        $model = new User();
        $model->username = 'foo';
        $this->assertEquals([
            'username' => 'foo',
        ], $model->getChangedAttributes());
    }
}
