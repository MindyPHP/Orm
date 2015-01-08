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

use Mindy\Tests\DatabaseTestCase;
use Tests\Models\CustomPk;

class CustomPrimaryKeyTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new CustomPk]);
    }

    public function tearDown()
    {
        $this->dropModels([new CustomPk]);
    }

    public function testSave()
    {
        $model = new CustomPk();
        $this->assertTrue($model->getIsNewRecord());
        $model->id = 1;
        $this->assertTrue($model->getIsNewRecord());
        $saved = $model->save();
        $this->assertTrue($saved);
        $this->assertFalse($model->getIsNewRecord());
        $model->id = 2;
        $this->assertTrue($model->getIsNewRecord());

        $model = CustomPk::objects()->filter(['pk' => 1])->get();
        $this->assertFalse($model->getIsNewRecord());
        $model->id = 3;
        $this->assertTrue($model->getIsNewRecord());

        $model = CustomPk::objects()->filter(['pk' => 1])->get();
        $this->assertFalse($model->getIsNewRecord());
        $model->pk = 4;
        $this->assertTrue($model->getIsNewRecord());
    }
}
