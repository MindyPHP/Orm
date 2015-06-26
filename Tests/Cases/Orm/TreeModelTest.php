<?php
/**
 * All rights reserved.
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 05/05/14.05.2014 18:57
 */

namespace Tests\Orm;

use Tests\Models\NestedModel;
use Tests\OrmDatabaseTestCase;

abstract class TreeModelTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new NestedModel];
    }

    public function testInit()
    {
        $model = new NestedModel;
        $this->assertTrue(array_key_exists("id", $model->getFieldsInit()));
        $this->assertTrue(array_key_exists("name", $model->getFieldsInit()));
        $this->assertTrue(array_key_exists("slug", $model->getFieldsInit()));
        $this->assertTrue(array_key_exists("lft", $model->getFieldsInit()));
        $this->assertTrue(array_key_exists("rgt", $model->getFieldsInit()));
        $this->assertTrue(array_key_exists("level", $model->getFieldsInit()));
        $this->assertTrue(array_key_exists("root", $model->getFieldsInit()));
        $this->assertTrue(array_key_exists("parent", $model->getFieldsInit()));
        $this->assertEquals(8, count($model->getFieldsInit()));
    }

    public function testInsert()
    {
        list($rootModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test']);
        $this->assertEquals(1, $rootModel->pk);

        $this->assertEquals(1, $rootModel->lft);
        $this->assertEquals(2, $rootModel->rgt);
        $this->assertEquals(1, $rootModel->level);
        $this->assertEquals(1, $rootModel->root);
        $this->assertNull($rootModel->parent);

        list($rootModelTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test1']);
        $this->assertEquals(2, $rootModelTwo->pk);

        $this->assertEquals(1, $rootModelTwo->lft);
        $this->assertEquals(2, $rootModelTwo->rgt);
        $this->assertEquals(1, $rootModelTwo->level);
        $this->assertEquals(2, $rootModelTwo->root);
        $this->assertNull($rootModelTwo->parent);

        list($nestedModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test2', 'parent' => $rootModelTwo]);
        $this->assertEquals(3, $nestedModel->pk);

        $this->assertEquals(2, $nestedModel->lft);
        $this->assertEquals(3, $nestedModel->rgt);
        $this->assertEquals(2, $nestedModel->level);
        $this->assertEquals(2, $nestedModel->root);
        $this->assertNotNull($nestedModel->parent);

        $this->assertEquals(4, NestedModel::objects()->get(['pk' => 2])->rgt);

        list($nestedTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test3', 'parent' => $rootModelTwo]);
        $this->assertEquals(4, $nestedTwo->pk);

        $this->assertEquals(4, $nestedTwo->lft);
        $this->assertEquals(5, $nestedTwo->rgt);
        $this->assertEquals(2, $nestedTwo->level);
        $this->assertEquals(2, $nestedTwo->root);
        $this->assertNotNull($nestedTwo->parent);

        $this->assertEquals(6, NestedModel::objects()->get(['pk' => 2])->rgt);

        list($threeLevelModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test4', 'parent' => $nestedTwo]);
        $this->assertEquals(5, $threeLevelModel->pk);

        $this->assertEquals(5, $threeLevelModel->lft);
        $this->assertEquals(6, $threeLevelModel->rgt);
        $this->assertEquals(3, $threeLevelModel->level);
        $this->assertEquals(2, $threeLevelModel->root);
        $this->assertNotNull($threeLevelModel->parent);

        $model = NestedModel::objects()->get(['pk' => 2]);
        $this->assertEquals(2, $model->objects()->filter(['lft__gte' => 3, 'rgt__lte' => 10])->count());

        $model = NestedModel::tree()->get(['pk' => 2]);
        $this->assertEquals(3, $model->tree()->descendants()->count());
        $this->assertEquals(4, $model->tree()->descendants($includeSelf = true)->count());

        // DELETE tests

        list($rootModelTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test1']);
        $this->assertEquals(2, $rootModelTwo->pk);
        $this->assertEquals(1, $rootModelTwo->lft);
        $this->assertEquals(8, $rootModelTwo->rgt);
        $this->assertEquals(4, $rootModelTwo->delete());

        list($rootModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test']);
        $this->assertEquals(1, $rootModel->pk);

        $this->assertEquals(1, $rootModel->lft);
        $this->assertEquals(2, $rootModel->rgt);
        $this->assertEquals(1, $rootModel->level);
        $this->assertEquals(1, $rootModel->root);
        $this->assertNull($rootModel->parent);

        $this->assertEquals(1, $rootModel->delete());
    }

    public function testUpdate()
    {
        list($rootModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test']);
        list($rootModelTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test1']);
        list($nestedModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test2', 'parent' => $rootModelTwo]);
        list($nestedTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test3', 'parent' => $rootModelTwo]);
        list($threeLevelModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test4', 'parent' => $nestedTwo]);

        $threeLevelModel->parent = $rootModel;
        $threeLevelModel->save();
        $this->assertEquals(2, $threeLevelModel->lft);
        $this->assertEquals(3, $threeLevelModel->rgt);
        $this->assertEquals(2, $threeLevelModel->level);
        $this->assertEquals(1, $threeLevelModel->root);

        $this->assertNotNull($threeLevelModel->parent);
        $threeLevelModel->parent = null;
        $this->assertNull($threeLevelModel->parent);
        $threeLevelModel->save();

        $this->assertEquals(3, $threeLevelModel->root);

        $rootModel = NestedModel::objects()->filter(['name' => 'test'])->get();
        $rootModel->parent = $rootModelTwo;
        $rootModel->save();
        $this->assertEquals(2, $rootModel->root);
    }

    public function testTree()
    {
        list($rootModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test']);
        list($rootModelTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test1']);
        list($nestedModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test2', 'parent' => $rootModelTwo]);
        list($nestedTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test3', 'parent' => $rootModelTwo]);
        list($threeLevelModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test4', 'parent' => $nestedTwo]);

        $data = NestedModel::tree()->asTree()->all();
        $this->assertEquals([
            [
                'id' => 1,
                'parent_id' => null,
                'lft' => 1,
                'rgt' => 2,
                'level' => 1,
                'root' => 1,
                'name' => 'test',
                'slug' => 'test',
                'items' => [],
            ],
            [
                'id' => 2,
                'parent_id' => null,
                'lft' => 1,
                'rgt' => 8,
                'level' => 1,
                'root' => 2,
                'name' => 'test1',
                'slug' => 'test1',
                'items' => [
                    [
                        'id' => 3,
                        'parent_id' => 2,
                        'lft' => 2,
                        'rgt' => 3,
                        'level' => 2,
                        'root' => 2,
                        'name' => 'test2',
                        'slug' => 'test1/test2',
                        'items' => [],
                    ],
                    [
                        'id' => 4,
                        'parent_id' => 2,
                        'lft' => 4,
                        'rgt' => 7,
                        'level' => 2,
                        'root' => 2,
                        'name' => 'test3',
                        'slug' => 'test1/test3',
                        'items' => [
                            [
                                'id' => 5,
                                'parent_id' => 4,
                                'lft' => 5,
                                'rgt' => 6,
                                'level' => 3,
                                'root' => 2,
                                'name' => 'test4',
                                'slug' => 'test1/test3/test4',
                                'items' => [],
                            ]
                        ]
                    ]
                ]
            ]
        ], $data);
    }

    /**
     * https://github.com/studio107/Mindy_Orm/issues/50
     */
    public function testFixIsLeaf()
    {
        // Create root
        list($root1, $created) = NestedModel::objects()->getOrCreate(['name' => 'root1']);
        $this->assertTrue(NestedModel::objects()->filter(['name' => 'root1'])->get()->getIsLeaf());

        // Create root2
        list($root2, $created) = NestedModel::objects()->getOrCreate(['name' => 'root2']);
        $this->assertTrue(NestedModel::objects()->filter(['name' => 'root2'])->get()->getIsLeaf());

        // Create nested record for root2
        list($nested, $created) = NestedModel::objects()->getOrCreate(['name' => 'nested', 'parent' => $root2]);
        // root1 is leaf = true
        $this->assertTrue(NestedModel::objects()->filter(['name' => 'root1'])->get()->getIsLeaf());
        // root2 is leaf = false (nested)
        $this->assertFalse(NestedModel::objects()->filter(['name' => 'root2'])->get()->getIsLeaf());
        // nested is leaf
        $this->assertTrue(NestedModel::objects()->filter(['name' => 'nested'])->get()->getIsLeaf());

        // Remove nested from root2
        NestedModel::tree()->filter(['name' => 'nested'])->delete();
        // Root1 is leaf - true
        $this->assertTrue(NestedModel::objects()->filter(['name' => 'root1'])->get()->getIsLeaf());
        // Root2 is leaf - true
        $this->assertTrue(NestedModel::objects()->filter(['name' => 'root2'])->get()->getIsLeaf());

        /**
         * again
         */
        NestedModel::objects()->truncate();

        // create root1
        list($root1, $created) = NestedModel::objects()->getOrCreate(['name' => 'root1']);
        // root1 is leaf
        $this->assertTrue(NestedModel::objects()->filter(['name' => 'root1'])->get()->getIsLeaf());

        // create root2
        list($root2, $created) = NestedModel::objects()->getOrCreate(['name' => 'root2']);
        // root2 is leaf
        $this->assertTrue(NestedModel::objects()->filter(['name' => 'root2'])->get()->getIsLeaf());

        // create nested in root2
        list($nested, $created) = NestedModel::objects()->getOrCreate(['name' => 'nested', 'parent' => $root2]);
        // create nested1 in nested
        list($nested1, $created) = NestedModel::objects()->getOrCreate(['name' => 'nested1', 'parent' => $nested]);
        // root1 is leaf (empty)
        $this->assertTrue(NestedModel::objects()->filter(['name' => 'root1'])->get()->getIsLeaf());
        // root2 is not leaf (nested, nested1)
        $this->assertFalse(NestedModel::objects()->filter(['name' => 'root2'])->get()->getIsLeaf());

        // nested is not leaf (nested1)
        $this->assertFalse(NestedModel::objects()->filter(['name' => 'nested'])->get()->getIsLeaf());

        // remove nested1 from nested
        NestedModel::tree()->filter(['name' => 'nested1'])->delete();
        $this->assertTrue(NestedModel::objects()->filter(['name' => 'root1'])->get()->getIsLeaf());
        // Root2 is not leaf (nested)
        $this->assertFalse(NestedModel::objects()->filter(['name' => 'root2'])->get()->getIsLeaf());

        // Nested is not leaf (nested1)
        $this->assertTrue(NestedModel::objects()->filter(['name' => 'nested'])->get()->getIsLeaf());

        // Root1 is leaf - true
        $this->assertTrue(NestedModel::objects()->filter(['name' => 'root1'])->get()->getIsLeaf());
        // Root2 is not leaf (nested)
        $this->assertFalse(NestedModel::objects()->filter(['name' => 'root2'])->get()->getIsLeaf());
    }
}
