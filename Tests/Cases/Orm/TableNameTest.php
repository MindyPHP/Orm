<?php

namespace Tests\Orm;

use Tests\Models\Product;
use Tests\Models\ProductList;
use Mindy\Tests\TestCase;

class TableNameTest extends TestCase
{
    public function testTableName()
    {
        $this->assertEquals('{{%tests_product}}', Product::tableName());
    }

    public function testLongTableName()
    {
        $this->assertEquals('{{%tests_product_list}}', ProductList::tableName());
    }
}
