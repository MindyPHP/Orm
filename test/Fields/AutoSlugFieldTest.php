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

namespace Mindy\Orm\Tests\Fields;

use Mindy\Orm\Tests\OrmDatabaseTestCase;
use Modules\Tests\Models\NestedModel;

abstract class AutoSlugFieldTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new NestedModel];
    }

    public function tearDown()
    {
        
    }

    public function testInit()
    {
        list($rootModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test']);
        $this->assertEquals(1, $rootModel->pk);
        $this->assertEquals('test', $rootModel->slug);

        list($rootModelTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test1']);
        $this->assertEquals(2, $rootModelTwo->pk);
        $this->assertEquals('test1', $rootModelTwo->slug);

        list($nestedModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test2', 'parent' => $rootModelTwo]);
        $this->assertEquals(3, $nestedModel->pk);
        $this->assertEquals('test1/test2', $nestedModel->slug);

        list($nestedTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test3', 'parent' => $rootModelTwo]);
        $this->assertEquals(4, $nestedTwo->pk);
        $this->assertEquals('test1/test3', $nestedTwo->slug);

        list($threeLevelModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test4', 'parent' => $nestedTwo]);
        $this->assertEquals(5, $threeLevelModel->pk);
        $this->assertEquals('test1/test3/test4', $threeLevelModel->slug);

        $this->assertEquals(5, NestedModel::objects()->count());
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

        // Play with parent attribute, bro.
        $threeLevelModel->parent = null;
        $this->assertNull($threeLevelModel->parent);
        $threeLevelModel->save();
        $this->assertEquals('test4', $threeLevelModel->slug);

        $threeLevelModel->parent = $nestedTwo;
        $this->assertEquals($nestedTwo->pk, $threeLevelModel->parent->pk);
        $threeLevelModel->save();
        $this->assertEquals('test1/test3/test4', $threeLevelModel->slug);
    }

    private function prepareTree()
    {
        list($rootModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test']);
        $this->assertEquals(1, $rootModel->pk);
        $this->assertEquals('test', $rootModel->slug);

        list($rootModelTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test1']);
        $this->assertEquals(2, $rootModelTwo->pk);
        $this->assertEquals('test1', $rootModelTwo->slug);

        list($nestedModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test2', 'parent' => $rootModelTwo]);
        $this->assertEquals(3, $nestedModel->pk);
        $this->assertEquals('test1/test2', $nestedModel->slug);

        list($nestedTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test3', 'parent' => $rootModelTwo]);
        $this->assertEquals(4, $nestedTwo->pk);
        $this->assertEquals('test1/test3', $nestedTwo->slug);

        list($threeLevelModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test4', 'parent' => $nestedTwo]);
        $this->assertEquals(5, $threeLevelModel->pk);
        $this->assertEquals('test1/test3/test4', $threeLevelModel->slug);

        $this->assertEquals(5, NestedModel::objects()->count());
    }

    public function testReplace()
    {
        $this->prepareTree();
        foreach (range(1, 5) as $i) {
            $this->assertNotNull(NestedModel::objects()->get(['id' => $i]));
        }

        $model = NestedModel::objects()->get(['name' => 'test1']);
        $model->slug = 'qwe';
        $this->assertEquals('test1', $model->getOldAttribute('slug'));
        $this->assertEquals('qwe', $model->getAttribute('slug'));
        $this->assertEquals('qwe', $model->slug);

        $model->save();
        $this->assertEquals(5, NestedModel::objects()->count());

        $test2 = NestedModel::objects()->get(['name' => 'test2']);
        $this->assertEquals('qwe/test2', $test2->slug);
        $this->assertEquals('qwe', $model->slug);

        $test3 = NestedModel::objects()->get(['name' => 'test3']);
        $this->assertEquals('qwe/test3', $test3->slug);

        $test4 = NestedModel::objects()->get(['name' => 'test4']);
        $this->assertEquals('qwe/test3/test4', $test4->slug);

        $test3->slug = 'www';
        $test3->save(['slug']);

        $test4 = NestedModel::objects()->get(['name' => 'test4']);
        $this->assertEquals('qwe/www/test4', $test4->slug);
    }
}
