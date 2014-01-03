<?php

namespace Tests\Orm;

use Tests\Models\Simple;
use Tests\Models\SimpleLongName;
use Tests\TestCase;
use WithoutNamespace;


class TableNameTest extends TestCase
{
    public function testTableName()
    {
        $this->assertEquals('simple', Simple::tableName());
    }

    public function testLongTableName()
    {
        $this->assertEquals('simple_long_name', SimpleLongName::tableName());
    }

    public function testWithoutNamespace()
    {
        $this->assertEquals('without_namespace', WithoutNamespace::tableName());
    }
}
