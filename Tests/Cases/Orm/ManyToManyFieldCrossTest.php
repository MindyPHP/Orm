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
 * @date 04/01/14.01.2014 20:41
 */

namespace Tests\Orm;


use Tests\DatabaseTestCase;
use Tests\Models\CrossManyModel;
use Tests\Models\ManyCrossModel;

class ManyToManyFieldCrossTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new CrossManyModel, new ManyCrossModel]);
    }

    public function tearDown()
    {
        $this->dropModels([new CrossManyModel, new ManyCrossModel]);
    }

    public function testSimple()
    {
        $manyModel = new ManyCrossModel();
        $manyModel->save();

        $this->assertInstanceOf('\Mindy\Orm\ManyToManyManager', $manyModel->cross_models);
        $this->assertEquals(0, $manyModel->cross_models->count());

        $crossModel = new CrossManyModel();
        $crossModel->save();

        $this->assertEquals(0, $crossModel->many_models->count());

        $crossModel->many_models->link($manyModel);

        $this->assertEquals(1, $crossModel->many_models->count());
        $this->assertEquals(1, $manyModel->cross_models->count());
    }
}
