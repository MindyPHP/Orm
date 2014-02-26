<?php
/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 04/01/14.01.2014 02:38
 */

namespace Tests\Orm;


use Tests\DatabaseTestCase;
use Tests\Models\SaveUpdateModel;

class SaveUpdateTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new SaveUpdateModel]);
    }

    public function tearDown()
    {
        $this->dropModels([new SaveUpdateModel]);
    }

    public function testSave()
    {
        $model = new SaveUpdateModel();
        $model->name = 'example';
        $model->price = 123;
        $model->is_default = true;
        $this->assertEquals(0, SaveUpdateModel::objects()->count());
        $this->assertTrue($model->isNewRecord);

        $this->assertTrue($model->isValid());
        $this->assertNull($model->pk);
        $this->assertEquals('example', $model->name);
        $this->assertEquals(123, $model->price);
        $this->assertEquals(true, $model->is_default);

        $saved = $model->save();
        $this->assertTrue($saved);
        $this->assertEquals(1, SaveUpdateModel::objects()->count());
        $this->assertFalse($model->isNewRecord);
        $this->assertEquals(1, $model->pk);
        $this->assertEquals('example', $model->name);
        $this->assertEquals(123, $model->price);
        $this->assertEquals(true, $model->is_default);
    }

    public function testUpdate()
    {
        $tmp = new SaveUpdateModel();
        $tmp->name = 'name 1';
        $tmp->price = 1;
        $tmp->is_default = true;

        $this->assertEquals(0, SaveUpdateModel::objects()->count());
        $this->assertTrue($tmp->isNewRecord);

        $this->assertEquals('name 1', $tmp->name);
        $this->assertEquals('1', $tmp->price);
        $this->assertEquals(true, $tmp->is_default);

        $saved = $tmp->save();
        $this->assertTrue($saved);
        $this->assertEquals(1, SaveUpdateModel::objects()->count());
        $this->assertFalse($tmp->isNewRecord);

        $this->assertEquals('name 1', $tmp->name);
        $this->assertEquals('1', $tmp->price);
        $this->assertEquals(true, $tmp->is_default);

        $tmp->name = 'name 2';
        $saved = $tmp->save();
        $this->assertTrue($saved);
        $this->assertEquals(1, SaveUpdateModel::objects()->count());
        $this->assertFalse($tmp->isNewRecord);

        $this->assertEquals('name 2', $tmp->name);
        $this->assertEquals('1', $tmp->price);
        $this->assertEquals(true, $tmp->is_default);

        $tmpFind = SaveUpdateModel::objects()->filter(['pk' => 1]);
        $this->assertEquals('name 2', $tmpFind->name);
        $this->assertEquals('1', $tmpFind->price);
        $this->assertEquals(true, $tmpFind->is_default);
    }
}
