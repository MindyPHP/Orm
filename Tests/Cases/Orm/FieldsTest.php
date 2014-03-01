<?php

namespace Tests\Orm;

use Mindy\Orm\Fields\CharField;
use Tests\Models\ModelFields;
use Tests\TestCase;

class FieldsTest extends TestCase
{
    public function testInitialization()
    {
        $model = new ModelFields();
        $this->assertEquals(1, count($model->getFields()));
        $this->assertEquals(2, count($model->getFieldsInit()));
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
