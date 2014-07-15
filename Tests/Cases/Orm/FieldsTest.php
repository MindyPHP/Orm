<?php

namespace Tests\Orm;

use Tests\Models\Category;
use Tests\TestCase;

class FieldsTest extends TestCase
{
    public function testInitialization()
    {
        $model = new Category();
        $this->assertEquals(2, count($model->getFields()));
        $this->assertEquals(4, count($model->getFieldsInit()));
    }

    /**
     * @expectedException \Exception
     */
    public function testUnknownFieldException()
    {
        $model = new Category();
        $model->getField('something');
    }

    public function testUnknownField()
    {
        $model = new Category();
        $field = $model->getField('something', false);
        $this->assertNull($field);
    }
}
