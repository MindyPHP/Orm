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

use Modules\Tests\Models\NestedModel;
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
        $fields = $model->getFieldsInit();
        $this->assertTrue(array_key_exists("id", $fields));
        $this->assertTrue(array_key_exists("name", $fields));
        $this->assertTrue(array_key_exists("slug", $fields));
        $this->assertTrue(array_key_exists("lft", $fields));
        $this->assertTrue(array_key_exists("rgt", $fields));
        $this->assertTrue(array_key_exists("level", $fields));
        $this->assertTrue(array_key_exists("root", $fields));
        $this->assertTrue(array_key_exists("parent", $fields));
        $this->assertEquals(8, count($fields));
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
    }

    private function buildTree()
    {
        /** @var \Mindy\Orm\TreeModel $root1 $root2 $nested $nested2 */

        // create root1
        $root1 = new NestedModel(['name' => 'root1']);
        $this->assertTrue($root1->save());
        // root1 is leaf
        $this->assertTrue($root1->getIsLeaf());
        $this->assertTrue($root1->getIsRoot());

        // create root2
        $root2 = new NestedModel(['name' => 'root2']);
        $this->assertTrue($root2->save());
        // root2 is leaf
        $this->assertTrue($root2->getIsLeaf());
        $this->assertTrue($root2->getIsRoot());

        // create nested1 in root2
        $nested1 = new NestedModel(['name' => 'nested1', 'parent' => $root2]);
        $this->assertTrue($nested1->save());
        $this->assertTrue($nested1->getIsLeaf());
        $this->assertFalse($nested1->getIsRoot());

        // create nested2 in nested1
        $nested2 = new NestedModel(['name' => 'nested2', 'parent' => $nested1]);
        $this->assertTrue($nested2->save());
        $this->assertTrue($nested2->getIsLeaf());
        $this->assertFalse($nested2->getIsRoot());
    }

    public function testFixIsLeafAutoRebuild()
    {
        /** @var \Mindy\Orm\TreeModel $root1 $root2 $nested $nested2 */
        $this->buildTree();

        // root1 isleaf == true
        $this->assertTrue(NestedModel::objects()->get(['name' => 'root1'])->getIsLeaf());
        // root2 isleaf == false (nested1, nested2)
        $this->assertFalse(NestedModel::objects()->get(['name' => 'root2'])->getIsLeaf());
        // nested1 isleaf == false (nested2)
        $this->assertFalse(NestedModel::objects()->get(['name' => 'nested1'])->getIsLeaf());

        // remove nested1 from nested
        NestedModel::tree()->filter(['name' => 'nested2'])->delete();
        $this->assertTrue(NestedModel::objects()->get(['name' => 'root1'])->getIsLeaf());
        // root2 isleaf == false (nested1)
        $this->assertFalse(NestedModel::objects()->get(['name' => 'root2'])->getIsLeaf());
        // nested1 isleaf == true
        $this->assertTrue(NestedModel::objects()->get(['name' => 'nested1'])->getIsLeaf());

        // nested1 is not leaf (nested1)
        $this->assertTrue(NestedModel::objects()->get(['name' => 'nested1'])->getIsLeaf());
        // Root1 is leaf - true
        $this->assertTrue(NestedModel::objects()->get(['name' => 'root1'])->getIsLeaf());
        // Root2 is not leaf (nested)
        $this->assertFalse(NestedModel::objects()->get(['name' => 'root2'])->getIsLeaf());
    }

    public function testRemoveTree()
    {
        list($rootModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test']);
        list($rootModelTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test1']);
        list($nestedModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test2', 'parent' => $rootModelTwo]);
        list($threeLevelFirstModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test3', 'parent' => $nestedModel]);
        list($nestedTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test4', 'parent' => $rootModelTwo]);
        list($nestedThree, $created) = NestedModel::objects()->getOrCreate(['name' => 'test5', 'parent' => $rootModelTwo]);
        list($threeLevelModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test6', 'parent' => $nestedThree]);

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
                'rgt' => 12,
                'level' => 1,
                'root' => 2,
                'name' => 'test1',
                'slug' => 'test1',
                'items' => [
                    [
                        'id' => 3,
                        'parent_id' => 2,
                        'lft' => 2,
                        'rgt' => 5,
                        'level' => 2,
                        'root' => 2,
                        'name' => 'test2',
                        'slug' => 'test1/test2',
                        'items' => [
                            [
                                'id' => 4,
                                'parent_id' => 3,
                                'lft' => 3,
                                'rgt' => 4,
                                'level' => 3,
                                'root' => 2,
                                'name' => 'test3',
                                'slug' => 'test1/test2/test3',
                                'items' => [],
                            ],
                        ],
                    ],
                    [
                        'id' => 5,
                        'parent_id' => 2,
                        'lft' => 6,
                        'rgt' => 7,
                        'level' => 2,
                        'root' => 2,
                        'name' => 'test4',
                        'slug' => 'test1/test4',
                        'items' => [],
                    ],
                    [
                        'id' => 6,
                        'parent_id' => 2,
                        'lft' => 8,
                        'rgt' => 11,
                        'level' => 2,
                        'root' => 2,
                        'name' => 'test5',
                        'slug' => 'test1/test5',
                        'items' => [
                            [
                                'id' => 7,
                                'parent_id' => 6,
                                'lft' => 9,
                                'rgt' => 10,
                                'level' => 3,
                                'root' => 2,
                                'name' => 'test6',
                                'slug' => 'test1/test5/test6',
                                'items' => [],
                            ]
                        ]
                    ]
                ]
            ]
        ], $data);

        $nested = NestedModel::objects()->filter(['id' => 5])->get();
        $nested->delete();

        $root = NestedModel::objects()->get(['id' => 2]);
        $this->assertEquals($root->lft, 1);
        $this->assertEquals($root->rgt, 12);

        $root = NestedModel::objects()->get(['id' => 6]);
        $this->assertEquals($root->lft, 8);
        $this->assertEquals($root->rgt, 11);

        NestedModel::objects()->filter(['id' => 7])->delete();
        $root = NestedModel::objects()->get(['id' => 6]);
        $this->assertEquals($root->lft, 8);
        $this->assertEquals($root->rgt, 9);

        $nested = NestedModel::objects()->filter(['id' => 3])->delete();
        $this->assertNull(NestedModel::objects()->filter(['id' => 4])->get());
    }

    public function testRemoveTreeAnother()
    {
        list($rootModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test']);

        list($nestedModelFirst, $created) = NestedModel::objects()->getOrCreate(['name' => 'test5', 'parent' => $rootModel]);
        list($nestedTwoSecond, $created) = NestedModel::objects()->getOrCreate(['name' => 'test6', 'parent' => $rootModel]);
        list($nestedThreeThird, $created) = NestedModel::objects()->getOrCreate(['name' => 'test7', 'parent' => $rootModel]);


        list($rootModelThree, $created) = NestedModel::objects()->getOrCreate(['name' => 'test8']);

        list($rootModelTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test1']);

        list($nestedModel, $created) = NestedModel::objects()->getOrCreate(['name' => 'test2', 'parent' => $rootModelTwo]);
        list($nestedTwo, $created) = NestedModel::objects()->getOrCreate(['name' => 'test3', 'parent' => $rootModelTwo]);
        list($nestedThree, $created) = NestedModel::objects()->getOrCreate(['name' => 'test4', 'parent' => $rootModelTwo]);


        $data = NestedModel::tree()->asTree()->all();
        $this->assertEquals([
            [
                'id' => 1,
                'parent_id' => null,
                'lft' => 1,
                'rgt' => 8,
                'level' => 1,
                'root' => 1,
                'name' => 'test',
                'slug' => 'test',
                'items' => [
                    [
                        'id' => 2,
                        'parent_id' => 1,
                        'lft' => 2,
                        'rgt' => 3,
                        'level' => 2,
                        'root' => 1,
                        'name' => 'test5',
                        'slug' => 'test/test5',
                        'items' => [],
                    ],
                    [
                        'id' => 3,
                        'parent_id' => 1,
                        'lft' => 4,
                        'rgt' => 5,
                        'level' => 2,
                        'root' => 1,
                        'name' => 'test6',
                        'slug' => 'test/test6',
                        'items' => [],
                    ],
                    [
                        'id' => 4,
                        'parent_id' => 1,
                        'lft' => 6,
                        'rgt' => 7,
                        'level' => 2,
                        'root' => 1,
                        'name' => 'test7',
                        'slug' => 'test/test7',
                        'items' => []
                    ]
                ],
            ],
            [
                'id' => 5,
                'parent_id' => null,
                'lft' => 1,
                'rgt' => 2,
                'level' => 1,
                'root' => 2,
                'name' => 'test8',
                'slug' => 'test8',
                'items' => [],
            ],
            [
                'id' => 6,
                'parent_id' => null,
                'lft' => 1,
                'rgt' => 8,
                'level' => 1,
                'root' => 3,
                'name' => 'test1',
                'slug' => 'test1',
                'items' => [
                    [
                        'id' => 7,
                        'parent_id' => 6,
                        'lft' => 2,
                        'rgt' => 3,
                        'level' => 2,
                        'root' => 3,
                        'name' => 'test2',
                        'slug' => 'test1/test2',
                        'items' => [],
                    ],
                    [
                        'id' => 8,
                        'parent_id' => 6,
                        'lft' => 4,
                        'rgt' => 5,
                        'level' => 2,
                        'root' => 3,
                        'name' => 'test3',
                        'slug' => 'test1/test3',
                        'items' => [],
                    ],
                    [
                        'id' => 9,
                        'parent_id' => 6,
                        'lft' => 6,
                        'rgt' => 7,
                        'level' => 2,
                        'root' => 3,
                        'name' => 'test4',
                        'slug' => 'test1/test4',
                        'items' => []
                    ]
                ]
            ]
        ], $data);

        $nested = NestedModel::objects()->filter(['id' => 4])->get();
        $nested->delete();

        $this->assertEquals([
                [
                    'id' => '1',
                    'parent_id' => NULL,
                    'lft' => '1',
                    'rgt' => '8',
                    'level' => '1',
                    'root' => '1',
                    'name' => 'test',
                    'slug' => 'test',
                    'items' =>
                        [
                                [
                                    'id' => '2',
                                    'parent_id' => '1',
                                    'lft' => '2',
                                    'rgt' => '3',
                                    'level' => '2',
                                    'root' => '1',
                                    'name' => 'test5',
                                    'slug' => 'test/test5',
                                    'items' =>
                                        [
                                        ],
                                ],
                                [
                                    'id' => '3',
                                    'parent_id' => '1',
                                    'lft' => '4',
                                    'rgt' => '5',
                                    'level' => '2',
                                    'root' => '1',
                                    'name' => 'test6',
                                    'slug' => 'test/test6',
                                    'items' =>
                                        [
                                        ],
                                ],
                        ],
                ],
                [
                    'id' => '5',
                    'parent_id' => NULL,
                    'lft' => '1',
                    'rgt' => '2',
                    'level' => '1',
                    'root' => '2',
                    'name' => 'test8',
                    'slug' => 'test8',
                    'items' =>
                        [
                        ],
                ],
                [
                    'id' => '6',
                    'parent_id' => NULL,
                    'lft' => '1',
                    'rgt' => '8',
                    'level' => '1',
                    'root' => '3',
                    'name' => 'test1',
                    'slug' => 'test1',
                    'items' =>
                        [
                                [
                                    'id' => '7',
                                    'parent_id' => '6',
                                    'lft' => '2',
                                    'rgt' => '3',
                                    'level' => '2',
                                    'root' => '3',
                                    'name' => 'test2',
                                    'slug' => 'test1/test2',
                                    'items' =>
                                        [
                                        ],
                                ],
                                [
                                    'id' => '8',
                                    'parent_id' => '6',
                                    'lft' => '4',
                                    'rgt' => '5',
                                    'level' => '2',
                                    'root' => '3',
                                    'name' => 'test3',
                                    'slug' => 'test1/test3',
                                    'items' =>
                                        [
                                        ],
                                ],
                                [
                                    'id' => '9',
                                    'parent_id' => '6',
                                    'lft' => '6',
                                    'rgt' => '7',
                                    'level' => '2',
                                    'root' => '3',
                                    'name' => 'test4',
                                    'slug' => 'test1/test4',
                                    'items' =>
                                        [
                                        ],
                                ],
                        ],
                ],
        ],NestedModel::tree()->asTree()->all());

    }
}
