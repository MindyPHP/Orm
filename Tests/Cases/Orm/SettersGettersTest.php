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
 * @date 04/01/14.01.2014 00:21
 */

namespace Tests\Orm;


use Tests\Models\Product;
use Tests\TestCase;


class SettersGettersTest extends TestCase
{
    public function testSetters()
    {
        $model = new Product();
        $model->name = 'example';
        $this->assertEquals('example', $model->name);

        $this->assertEquals('SIMPLE', $model->type);
        $model->type = '123';
        $this->assertEquals('123', $model->type);
    }

    /**
     * @expectedException \Exception
     */
    public function testSettersException()
    {
        $model = new Product();
        $model->this_property_does_not_exists = 'example';
    }

    public function testGetters()
    {
        $model = new Product();
        $this->assertEquals('SIMPLE', $model->type);

        // test default field value
        $this->assertEquals('Product', $model->name);

        $model->name = '123';
        $this->assertEquals('123', $model->name);
    }
}
