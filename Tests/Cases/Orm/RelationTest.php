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
 * @date 04/01/14.01.2014 01:15
 */

namespace Tests\Orm;

use Mindy\Tests\DatabaseTestCase;
use Tests\Models\Product;

class RelationTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new Product]);
    }

    public function tearDown()
    {
        $this->dropModels([new Product]);
    }

    public function testInit()
    {
        $model = new Product();
        $schema = $model->getTableSchema();
        $this->assertTrue(isset($schema->columns['id']));
        $this->assertTrue(isset($schema->columns['category_id']));
    }

    public function testForeignKey()
    {
        $model = new Product();
        $fk = $model->getField("category");
        $this->assertInstanceOf('\Mindy\Orm\Fields\ForeignField', $fk);
        $this->assertNull($model->category);
    }
}
