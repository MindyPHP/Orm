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
 * @date 27/05/14.05.2014 18:02
 */

namespace Tests\Orm\Fields;


use Tests\DatabaseTestCase;
use Tests\Models\NestedModel;

class AutoSlugFieldTest extends DatabaseTestCase
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
        $rootModel = NestedModel::objects()->getOrCreate(['name' => 'test']);
        $this->assertEquals('test', $rootModel->slug);

        $rootModelTwo = NestedModel::objects()->getOrCreate(['name' => 'test1']);
        $this->assertEquals('test1', $rootModelTwo->slug);

        $nestedModel = NestedModel::objects()->getOrCreate(['name' => 'test2', 'parent' => $rootModelTwo]);
        $this->assertEquals('test1/test2', $nestedModel->slug);

        $nestedTwo = NestedModel::objects()->getOrCreate(['name' => 'test3', 'parent' => $rootModelTwo]);
        $this->assertEquals('test1/test3', $nestedTwo->slug);

        $threeLevelModel = NestedModel::objects()->getOrCreate(['name' => 'test4', 'parent' => $nestedTwo]);
        $this->assertEquals('test1/test3/test4', $threeLevelModel->slug);
    }

    public function testInitTwo()
    {
        $rootModel = new NestedModel(['name' => 'test']);
        $rootModel->save();
        $this->assertEquals('test', $rootModel->slug);

        $rootModelTwo = new NestedModel(['name' => 'test1']);
        $rootModelTwo->save();
        $this->assertEquals('test1', $rootModelTwo->slug);

        $nestedModel = new NestedModel(['name' => 'test2', 'parent' => $rootModelTwo]);
        $nestedModel->save();
        $this->assertEquals('test1/test2', $nestedModel->slug);

        $nestedTwo = new NestedModel(['name' => 'test3', 'parent' => $rootModelTwo]);
        $nestedTwo->save();
        $this->assertEquals('test1/test3', $nestedTwo->slug);

        $threeLevelModel = new NestedModel(['name' => 'test4', 'parent' => $nestedTwo]);
        $threeLevelModel->save();
        $this->assertEquals('test1/test3/test4', $threeLevelModel->slug);
    }

    public function testReplace()
    {
        $this->testInit();
        $this->assertEquals(5, NestedModel::objects()->count());

        $model = NestedModel::objects()->filter(['name' => 'test1'])->get();
        $model->slug = 'qwe';
        $this->assertEquals('test1', $model->getOldAttribute('slug'));
        $model->save();
        $this->assertEquals('qwe', $model->getOldAttribute('slug'));
        $this->assertEquals('qwe', $model->getAttribute('slug'));
        $this->assertEquals('qwe', $model->slug);
        $test2 = NestedModel::objects()->filter(['name' => 'test2'])->get();
        $this->assertEquals('qwe/test2', $test2->slug);
        $test3 = NestedModel::objects()->filter(['name' => 'test3'])->get();
        $this->assertEquals('qwe/test3', $test3->slug);
        $test4 = NestedModel::objects()->filter(['name' => 'test4'])->get();
        $this->assertEquals('qwe/test3/test4', $test4->slug);

        $test3->slug = 'www';
        $test3->save(['slug']);
        $test4 = NestedModel::objects()->filter(['name' => 'test4'])->get();
        $this->assertEquals('qwe/www/test4', $test4->slug);
    }
}
