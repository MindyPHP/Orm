<?php

namespace Tests\Orm;

use Tests\DatabaseTestCase;
use Tests\Models\CreateModel;
use Tests\Models\ForeignKeyModel;

class FieldsAndAttributesGetterSetterTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([
            new ForeignKeyModel(),
            new CreateModel()
        ]);
    }

    public function tearDown()
    {
        $this->dropModels([
            new ForeignKeyModel()
        ]);

        parent::tearDown();
    }

    public function testGetter()
    {
        $create = new CreateModel();
        $create->name = 'test';
        $create->save();

        $model = new ForeignKeyModel();
        $this->assertNull($model->something);

        $model->something = $create;
        $model->save();

        $this->assertInstanceOf('\Tests\Models\CreateModel', $model->something);
        $this->assertTrue(is_numeric($model->something_id));
    }

    public function testSetter()
    {
        $create = new CreateModel();
        $create->name = 'test';
        $create->save();

        $model = new ForeignKeyModel();
        $this->assertNull($model->something);

        $model->something_id = $create->getPk();
        $model->save();

        $this->assertInstanceOf('\Tests\Models\CreateModel', $model->something);
        $this->assertTrue(is_numeric($model->something_id));
    }
}
