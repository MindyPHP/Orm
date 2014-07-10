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
 * @date 04/01/14.01.2014 00:53
 */

namespace Tests\Orm;


use Tests\DatabaseTestCase;
use Tests\Models\Category;


class PkTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new Category]);
    }

    public function testPk()
    {
        $category = new Category();
        $fields = $category->getFieldsInit();

        $this->assertEquals(['id', 'name', 'products'], array_keys($fields));

        $this->assertInstanceOf('\Mindy\Orm\Fields\AutoField', $fields['id']);
        $this->assertNull($category->id);
        $this->assertNull($category->pk);
    }
}
