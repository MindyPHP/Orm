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
 * @date 04/01/14.01.2014 02:38
 */

namespace Tests\Orm;


use Tests\DatabaseTestCase;
use Tests\Models\Hits;
use Tests\Models\User;

class UpdateCountersTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new Hits]);
    }

    public function tearDown()
    {
        $this->dropModels([new Hits]);
    }

    public function testSave()
    {
        $model = new Hits();
        $model->save();
        $this->assertEquals(1, $model->pk);
        $this->assertEquals(0, $model->hits);
        $this->assertEquals(0, Hits::objects()->get(['pk' => 1])->hits);

        $model->objects()->updateCounters(['hits' => 1]);
        $this->assertEquals(1, Hits::objects()->get(['pk' => 1])->hits);
    }
}
