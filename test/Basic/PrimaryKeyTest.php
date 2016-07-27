<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 25/02/15 17:20
 */

namespace Mindy\Orm\Tests\Basic;

use Mindy\Orm\Fields\AutoField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Model;
use Mindy\Orm\Tests\OrmDatabaseTestCase;

class DefaultPrimaryKey extends Model
{

}

class CustomPrimaryKey extends Model
{
    public static function getFields()
    {
        return [
            'id' => [
                'class' => IntField::class,
                'primary' => true
            ],
        ];
    }
}

class PrimaryKeyTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [
            new DefaultPrimaryKey,
            new CustomPrimaryKey
        ];
    }


    public function testFieldPrimaryKey()
    {
        $model = new DefaultPrimaryKey();
        $fields = $model->getFieldsInit();
        $this->assertEquals(['id'], array_keys($fields));

        $this->assertInstanceOf(AutoField::class, $fields['id']);
        $this->assertNull($model->id);
        $this->assertNull($model->pk);

        $model = new CustomPrimaryKey();
        $fields = $model->getFieldsInit();
        $this->assertEquals(['id'], array_keys($fields));

        $this->assertInstanceOf(IntField::class, $fields['id']);
        $this->assertNull($model->id);
        $this->assertNull($model->pk);
    }

    public function testPrimaryKey()
    {
        $model = new DefaultPrimaryKey();
        $this->assertNull($model->pk);
        $this->assertNull($model->id);

        $model->pk = 1;
        $this->assertSame(1, $model->pk);
        $this->assertSame(1, $model->id);
        $this->assertSame($model->pk, $model->id);

        $model->save();
        $this->assertSame(1, $model->pk);
        $this->assertSame(1, $model->id);
        $this->assertSame($model->pk, $model->id);

        $model->pk = 2;
        $this->assertSame(2, $model->pk);
        $this->assertSame(2, $model->id);
        $this->assertSame($model->pk, $model->id);

        $model->save();
        $this->assertSame(2, $model->pk);
        $this->assertSame(2, $model->id);
        $this->assertSame($model->pk, $model->id);

        $this->assertEquals(2, $model->objects()->count());
        $this->assertEquals(1, DefaultPrimaryKey::objects()->get(['pk' => 1])->pk);
    }

    public function testCustomPrimaryKey()
    {
        $model = new CustomPrimaryKey();
        $this->assertNull($model->pk);
        $this->assertNull($model->id);

        $model->pk = 1;
        $this->assertSame(1, $model->pk);
        $this->assertSame(1, $model->id);
        $this->assertSame($model->pk, $model->id);

        $model->save();
        $this->assertSame(1, $model->pk);
        $this->assertSame(1, $model->id);
        $this->assertSame($model->pk, $model->id);

        $model->pk = 2;
        $this->assertSame(2, $model->pk);
        $this->assertSame(2, $model->id);
        $this->assertSame($model->pk, $model->id);

        $model->save();
        $this->assertSame(2, $model->pk);
        $this->assertSame(2, $model->id);
        $this->assertSame($model->pk, $model->id);

        $this->assertEquals(2, $model->objects()->count());
        $this->assertEquals(1, CustomPrimaryKey::objects()->get(['pk' => 1])->pk);
    }
}
