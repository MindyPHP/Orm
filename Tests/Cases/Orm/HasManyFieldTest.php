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
use Tests\Models\HasManyModel;
use Tests\Models\FkModel;

class HasManyFieldTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new FkModel, new HasManyModel]);
    }

    public function tearDown()
    {
        $this->dropModels([new FkModel, new HasManyModel]);
    }

    public function testSimple()
    {
        $hasManyModel = new HasManyModel();
        $hasManyModel->save();

        $this->assertEquals(0, $hasManyModel->many->count());

        $fkModelOne = new FkModel();
        $fkModelOne->fk = $hasManyModel;
        $fkModelOne->save();

        $this->assertEquals(1, $hasManyModel->many->count());

        $fkModelTwo = new FkModel();
        $fkModelTwo->save();

        $this->assertEquals(1, $hasManyModel->many->count());

        $fkModelTwo->fk = $hasManyModel;
        $fkModelTwo->save();

        $this->assertEquals(2, $hasManyModel->many->count());

    }
}
