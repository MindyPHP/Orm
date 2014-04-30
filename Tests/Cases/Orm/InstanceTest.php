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
use Tests\Models\InstanceTestModel;
use Tests\Models\User;


class InstanceTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([
            new InstanceTestModel,
        ]);
    }

    public function tearDown()
    {
        $this->dropModels([
            new InstanceTestModel,
        ]);
    }

    public function testInstance()
    {
        $this->assertInstanceOf('\Mindy\Orm\Manager', InstanceTestModel::objects());
        $model = new InstanceTestModel;
        $this->assertEquals(123, $model->objects());
    }
}
