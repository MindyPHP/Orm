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


use Tests\Models\GettersModel;
use Tests\Models\SettersModel;
use Tests\TestCase;

class SettersGettersTest extends TestCase
{
    public function testSetters()
    {
        $model = new SettersModel();
        $model->name = 'example';
        $this->assertEquals('example', $model->name);

        $this->assertEquals('test', $model->test);
        $model->test = '123';
        $this->assertEquals('123', $model->test);
    }

    /**
     * @expectedException \Exception
     */
    public function testSettersException()
    {
        $model = new SettersModel();
        $model->qwe = 'example';
    }

    public function testGetters()
    {
        $model = new GettersModel();
        $this->assertEquals('test', $model->test);

        // test default field value
        $this->assertEquals('example', $model->name);

        $model->name = '123';
        $this->assertEquals('123', $model->name);
    }
}
