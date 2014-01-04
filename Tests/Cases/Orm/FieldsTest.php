<?php

namespace Tests\Orm;

use Mindy\Db\Fields\CharField;
use Tests\Models\ModelFields;
use Tests\TestCase;

class FieldsTest extends TestCase
{
    public function testInitialization()
    {
        $model = new ModelFields();
        $this->assertEquals([
            'name' => new CharField()
        ], $model->getFields());

        $this->assertEquals([
            'id' => new $model->autoField(),
            'name' => new CharField(),
        ], $model->getFieldsInit());
    }

    /**
     * @expectedException \Exception
     */
    public function testUnknownFieldException()
    {
        $model = new ModelFields();
        $model->getField('something');
    }

    public function testUnknownField()
    {
        $model = new ModelFields();
        $field = $model->getField('something', false);
        $this->assertNull($field);
    }
}
