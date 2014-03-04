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
use Tests\Models\Category;

class ManagerTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new Category]);
    }

    public function tearDown()
    {
        $this->dropModels([new Category]);
    }

    public function testAll()
    {
        $model_one = new Category();
        $model_one->name = 'one';
        $model_one->save();

        $model_two = new Category();
        $model_two->name = 'two';
        $model_two->save();

        $this->assertEquals(2, count(Category::objects()->all()));
    }

//    public function testGet()
//    {
//        $model_one = new ManagerModel();
//        $model_one->name = 'one';
//        $model_one->save();
//
//        $model_two = new ManagerModel();
//        $model_two->name = 'two';
//        $model_two->save();
//
//        $founded = ManagerModel::objects()->get(['name' => 'one']);
//        $this->assertInstanceOf('ManagerModel', $founded);
//        $this->assertEquals('one', $founded->name);
//    }
//
//    public function testFilter()
//    {
//        $model_one = new ManagerModel();
//        $model_one->name = 'one';
//        $model_one->save();
//
//        $model_two = new ManagerModel();
//        $model_two->name = 'two';
//        $model_two->save();
//
//        $this->assertEquals(1, count(ManagerModel::objects()->filter(['name' => 'one'])->all()));
//    }
//
//    public function testExclude()
//    {
//        $model_one = new ManagerModel();
//        $model_one->name = 'one';
//        $model_one->save();
//
//        $model_two = new ManagerModel();
//        $model_two->name = 'two';
//        $model_two->save();
//
//        $this->assertEquals(1, count(ManagerModel::objects()->exclude(['name' => 'one'])->all()));
//    }
}
