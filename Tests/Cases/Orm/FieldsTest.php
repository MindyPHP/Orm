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
    }
}
