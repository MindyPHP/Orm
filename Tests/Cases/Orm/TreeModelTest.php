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
 * @date 05/05/14.05.2014 18:57
 */

namespace Tests\Orm;


use Tests\DatabaseTestCase;
use Tests\Models\NestedModel;

class TreeModelTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->initModels([new NestedModel]);
    }

    public function tearDown()
    {
        $this->dropModels([new NestedModel]);
    }

    public function testInit()
    {
        $model = new NestedModel;
        $this->assertEquals(7, count($model->getFieldsInit()));

        $this->assertTrue(array_key_exists("name", $model->getFieldsInit()));

        $this->assertTrue(array_key_exists("lft", $model->getFieldsInit()));
        $this->assertTrue(array_key_exists("rgt", $model->getFieldsInit()));
        $this->assertTrue(array_key_exists("level", $model->getFieldsInit()));
        $this->assertTrue(array_key_exists("root", $model->getFieldsInit()));
        $this->assertTrue(array_key_exists("parent", $model->getFieldsInit()));
    }

    public function testInsert()
    {
        $rootModel = NestedModel::objects()->getOrCreate(['name' => 'test']);
        $this->assertEquals(1, $rootModel->pk);

        $this->assertEquals(1, $rootModel->lft);
        $this->assertEquals(2, $rootModel->rgt);
        $this->assertEquals(0, $rootModel->level);
        $this->assertEquals(1, $rootModel->root);
        $this->assertNull($rootModel->parent);

        $rootModelTwo = NestedModel::objects()->getOrCreate(['name' => 'test1']);
        $this->assertEquals(2, $rootModelTwo->pk);

        $this->assertEquals(3, $rootModelTwo->lft);
        $this->assertEquals(4, $rootModelTwo->rgt);
        $this->assertEquals(0, $rootModelTwo->level);
        $this->assertEquals(2, $rootModelTwo->root);
        $this->assertNull($rootModelTwo->parent);

        $nestedModel = NestedModel::objects()->getOrCreate(['name' => 'test2', 'parent' => $rootModelTwo]);
        $this->assertEquals(3, $nestedModel->pk);

        $this->assertEquals(4, $nestedModel->lft);
        $this->assertEquals(5, $nestedModel->rgt);
        $this->assertEquals(1, $nestedModel->level);
        $this->assertEquals(2, $nestedModel->root);
        $this->assertNotNull($nestedModel->parent);

        $this->assertEquals(6, NestedModel::objects()->get(['pk' => 2])->rgt);

        $nestedTwo = NestedModel::objects()->getOrCreate(['name' => 'test3', 'parent' => $rootModelTwo]);
        $this->assertEquals(4, $nestedTwo->pk);

        $this->assertEquals(6, $nestedTwo->lft);
        $this->assertEquals(7, $nestedTwo->rgt);
        $this->assertEquals(1, $nestedTwo->level);
        $this->assertEquals(2, $nestedTwo->root);
        $this->assertNotNull($nestedTwo->parent);

        $this->assertEquals(8, NestedModel::objects()->get(['pk' => 2])->rgt);

        $threeLevelModel = NestedModel::objects()->getOrCreate(['name' => 'test4', 'parent' => $nestedTwo]);
        $this->assertEquals(5, $threeLevelModel->pk);

        $this->assertEquals(7, $threeLevelModel->lft);
        $this->assertEquals(8, $threeLevelModel->rgt);
        $this->assertEquals(2, $threeLevelModel->level);
        $this->assertEquals(2, $threeLevelModel->root);
        $this->assertNotNull($threeLevelModel->parent);

        $model = NestedModel::objects()->get(['pk' => 2]);
        $this->assertEquals(4, $model->objects()->filter(['lft__gte' => 3, 'rgt__lte' => 10])->count());

        $model = NestedModel::tree()->get(['pk' => 2]);
        $this->assertEquals(3, $model->tree()->descendants()->count());
        $this->assertEquals(4, $model->tree()->descendants($includeSelf = true)->count());
    }
}
