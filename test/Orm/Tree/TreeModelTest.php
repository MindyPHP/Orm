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

namespace Mindy\Tests\Orm\Tree;

use Mindy\Tests\Orm\Models\NestedModel;
use Mindy\Tests\Orm\OrmDatabaseTestCase;

abstract class TreeModelTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new NestedModel];
    }

    public function testSaveNewRoot()
    {
        $model = new NestedModel(['name' => 'root']);
        $this->assertTrue($model->save());
        $this->assertEquals(1, $model->level);
        $this->assertEquals(1, $model->pk);
        $this->assertEquals(1, $model->id);
        $this->assertEquals(1, $model->lft);
        $this->assertEquals(1, $model->level);
        $this->assertEquals(1, $model->root);
        $this->assertEquals(2, $model->rgt);
    }

    public function testSaveNewChildToRoot()
    {
        $model = new NestedModel(['name' => 'root']);
        $this->assertTrue($model->save());
        $this->assertEquals(1, $model->level);
        $this->assertEquals(1, $model->pk);
        $this->assertEquals(1, $model->id);
        $this->assertEquals(1, $model->root);
        $this->assertEquals(1, $model->lft);
        $this->assertEquals(2, $model->rgt);

        $child = new NestedModel(['name' => 'child', 'parent' => $model]);
        $this->assertTrue($child->save());
        $this->assertEquals(2, $child->pk);
        $this->assertEquals(2, $child->id);
        $this->assertEquals(2, $child->level);
        $this->assertEquals(1, $child->root);
        $this->assertEquals(2, $child->lft);
        $this->assertEquals(3, $child->rgt);

        /** @var \Mindy\Orm\TreeModel $root */
        $root = NestedModel::objects()->get(['pk' => 1]);
        $this->assertEquals(1, $root->level);
        $this->assertEquals(1, $root->pk);
        $this->assertEquals(1, $root->id);
        $this->assertEquals(1, $root->root);
        $this->assertEquals(1, $root->lft);
        $this->assertEquals(4, $root->rgt);
    }

    public function testMoveChildFromRootToRoot()
    {
        $model = new NestedModel(['name' => 'root']);
        $this->assertTrue($model->save());
        $this->assertEquals(1, $model->level);
        $this->assertEquals(1, $model->pk);
        $this->assertEquals(1, $model->id);
        $this->assertEquals(1, $model->root);
        $this->assertEquals(1, $model->lft);
        $this->assertEquals(2, $model->rgt);

        $child = new NestedModel(['name' => 'child', 'parent' => $model]);
        $this->assertTrue($child->save());
        $this->assertEquals(2, $child->pk);
        $this->assertEquals(2, $child->id);
        $this->assertEquals(2, $child->level);
        $this->assertEquals(1, $child->root);
        $this->assertEquals(2, $child->lft);
        $this->assertEquals(3, $child->rgt);

        /** @var \Mindy\Orm\TreeModel $root */
        $root = NestedModel::objects()->get(['pk' => 1]);
        $this->assertEquals(1, $root->level);
        $this->assertEquals(1, $root->pk);
        $this->assertEquals(1, $root->id);
        $this->assertEquals(1, $root->root);
        $this->assertEquals(1, $root->lft);
        $this->assertEquals(4, $root->rgt);

        $newRoot = new NestedModel(['name' => 'new_root']);
        $this->assertTrue($newRoot->save());

        $child->parent = $newRoot;
        $this->assertTrue($child->save());
        $this->assertEquals(2, $child->pk);
        $this->assertEquals(2, $child->id);
        $this->assertEquals(2, $child->level);
        $this->assertEquals(2, $child->root);
        $this->assertEquals(2, $child->lft);
        $this->assertEquals(3, $child->rgt);

        /** @var \Mindy\Orm\TreeModel $firstRoot */
        $firstRoot = NestedModel::objects()->get(['pk' => 1]);
        $this->assertEquals(1, $firstRoot->level);
        $this->assertEquals(1, $firstRoot->pk);
        $this->assertEquals(1, $firstRoot->id);
        $this->assertEquals(1, $firstRoot->root);
        $this->assertEquals(1, $firstRoot->lft);
        $this->assertEquals(2, $firstRoot->rgt);
    }

    public function testMoveChildFromRootToAnotherNode()
    {
        $model = new NestedModel(['name' => 'root']);
        $this->assertTrue($model->save());
        $this->assertEquals(1, $model->level);
        $this->assertEquals(1, $model->pk);
        $this->assertEquals(1, $model->id);
        $this->assertEquals(1, $model->root);
        $this->assertEquals(1, $model->lft);
        $this->assertEquals(2, $model->rgt);

        $child = new NestedModel(['name' => 'child', 'parent' => $model]);
        $this->assertTrue($child->save());
        $this->assertEquals(2, $child->pk);
        $this->assertEquals(2, $child->id);
        $this->assertEquals(2, $child->level);
        $this->assertEquals(1, $child->root);
        $this->assertEquals(2, $child->lft);
        $this->assertEquals(3, $child->rgt);

        $level1 = new NestedModel(['name' => 'level1']);
        $this->assertTrue($level1->save());
        $this->assertEquals(2, $level1->root);

        $level2 = new NestedModel(['name' => 'level2', 'parent' => $level1]);
        $this->assertTrue($level2->save());

        $child->parent = $level2;
        $this->assertTrue($child->save());
        $this->assertEquals(2, $child->pk);
        $this->assertEquals(2, $child->id);
        $this->assertEquals(3, $child->level);
        $this->assertEquals(2, $child->root);
        $this->assertEquals(3, $child->lft);
        $this->assertEquals(4, $child->rgt);

        /** @var \Mindy\Orm\TreeModel $level1 */
        $level1 = NestedModel::objects()->get(['name' => 'level1']);
        $this->assertTrue($level1->save());
        $this->assertEquals(3, $level1->pk);
        $this->assertEquals(3, $level1->id);
        $this->assertEquals(1, $level1->level);
        $this->assertEquals(2, $level1->root);
        $this->assertEquals(1, $level1->lft);
        $this->assertEquals(6, $level1->rgt);
    }

    public function testMoveRootToAnotherRoot()
    {
        $model = new NestedModel(['name' => 'root1']);
        $this->assertTrue($model->save());
        $this->assertEquals(1, $model->level);
        $this->assertEquals(1, $model->pk);
        $this->assertEquals(1, $model->id);
        $this->assertEquals(1, $model->root);
        $this->assertEquals(1, $model->lft);
        $this->assertEquals(2, $model->rgt);

        $child = new NestedModel(['name' => 'child']);
        $this->assertTrue($child->save());
        $this->assertEquals(1, $child->level);
        $this->assertEquals(2, $child->pk);
        $this->assertEquals(2, $child->id);
        $this->assertEquals(2, $child->root);
        $this->assertEquals(1, $child->lft);
        $this->assertEquals(2, $child->rgt);

        $child->parent = $model;
        $this->assertTrue($child->save());
        $this->assertEquals(2, $child->level);
        $this->assertEquals(2, $child->pk);
        $this->assertEquals(2, $child->id);
        $this->assertEquals(1, $child->root);
        $this->assertEquals(2, $child->lft);
        $this->assertEquals(3, $child->rgt);

        /** @var \Mindy\Orm\TreeModel $root */
        $root = NestedModel::objects()->get(['pk' => 1]);
        $this->assertTrue($root->save());
        $this->assertEquals(1, $root->level);
        $this->assertEquals(1, $root->pk);
        $this->assertEquals(1, $root->id);
        $this->assertEquals(1, $root->root);
        $this->assertEquals(1, $root->lft);
        $this->assertEquals(4, $root->rgt);
    }

    public function testDeleteRoot()
    {
        $root1 = new NestedModel(['name' => 'root1']);
        $this->assertTrue($root1->save());
        $this->assertEquals(1, $root1->level);
        $this->assertEquals(1, $root1->pk);
        $this->assertEquals(1, $root1->id);
        $this->assertEquals(1, $root1->root);
        $this->assertEquals(1, $root1->lft);
        $this->assertEquals(2, $root1->rgt);

        $root2 = new NestedModel(['name' => 'root2']);
        $this->assertTrue($root2->save());
        $this->assertEquals(1, $root2->level);
        $this->assertEquals(2, $root2->pk);
        $this->assertEquals(2, $root2->id);
        $this->assertEquals(2, $root2->root);
        $this->assertEquals(1, $root2->lft);
        $this->assertEquals(2, $root2->rgt);

        $root1->delete();

        /** @var \Mindy\Orm\TreeModel $root2 */
        $root2 = NestedModel::objects()->get(['name' => 'root2']);
        $this->assertTrue($root2->save());
        $this->assertEquals(1, $root2->level);
        $this->assertEquals(2, $root2->pk);
        $this->assertEquals(2, $root2->id);
        $this->assertEquals(2, $root2->root);
        $this->assertEquals(1, $root2->lft);
        $this->assertEquals(2, $root2->rgt);
    }

    public function testDeleteNodeFromRoot()
    {
        $root1 = new NestedModel(['name' => 'root1']);
        $this->assertTrue($root1->save());
        $this->assertEquals(1, $root1->level);
        $this->assertEquals(1, $root1->pk);
        $this->assertEquals(1, $root1->id);
        $this->assertEquals(1, $root1->root);
        $this->assertEquals(1, $root1->lft);
        $this->assertEquals(2, $root1->rgt);

        $child = new NestedModel(['name' => 'child', 'parent' => $root1]);
        $this->assertTrue($child->save());
        $this->assertEquals(2, $child->level);
        $this->assertEquals(2, $child->pk);
        $this->assertEquals(2, $child->id);
        $this->assertEquals(1, $child->root);
        $this->assertEquals(2, $child->lft);
        $this->assertEquals(3, $child->rgt);

        $child->delete();

        /** @var \Mindy\Orm\TreeModel $root1 */
        $root1 = NestedModel::objects()->get(['name' => 'root1']);
        $this->assertTrue($root1->save());
        $this->assertEquals(1, $root1->level);
        $this->assertEquals(1, $root1->pk);
        $this->assertEquals(1, $root1->id);
        $this->assertEquals(1, $root1->root);
        $this->assertEquals(1, $root1->lft);
        $this->assertEquals(2, $root1->rgt);
    }

    public function testDeleteNodeFromRootViaQuerySet()
    {
        $root1 = new NestedModel(['name' => 'root1']);
        $this->assertTrue($root1->save());
        $this->assertEquals(1, $root1->level);
        $this->assertEquals(1, $root1->pk);
        $this->assertEquals(1, $root1->id);
        $this->assertEquals(1, $root1->root);
        $this->assertEquals(1, $root1->lft);
        $this->assertEquals(2, $root1->rgt);

        $child = new NestedModel(['name' => 'child', 'parent' => $root1]);
        $this->assertTrue($child->save());
        $this->assertEquals(2, $child->level);
        $this->assertEquals(2, $child->pk);
        $this->assertEquals(2, $child->id);
        $this->assertEquals(1, $child->root);
        $this->assertEquals(2, $child->lft);
        $this->assertEquals(3, $child->rgt);

        NestedModel::objects()->filter(['name' => 'child'])->delete();

        /** @var \Mindy\Orm\TreeModel $root1 */
        $root1 = NestedModel::objects()->get(['name' => 'root1']);
        $this->assertTrue($root1->save());
        $this->assertEquals(1, $root1->level);
        $this->assertEquals(1, $root1->pk);
        $this->assertEquals(1, $root1->id);
        $this->assertEquals(1, $root1->root);
        $this->assertEquals(1, $root1->lft);
        $this->assertEquals(2, $root1->rgt);
    }

    public function testDeleteRootWithChildren()
    {
        $root1 = new NestedModel(['name' => 'root1']);
        $this->assertTrue($root1->save());
        $this->assertEquals(1, $root1->level);
        $this->assertEquals(1, $root1->pk);
        $this->assertEquals(1, $root1->id);
        $this->assertEquals(1, $root1->root);
        $this->assertEquals(1, $root1->lft);
        $this->assertEquals(2, $root1->rgt);

        $child = new NestedModel(['name' => 'child', 'parent' => $root1]);
        $this->assertTrue($child->save());
        $this->assertEquals(2, $child->level);
        $this->assertEquals(2, $child->pk);
        $this->assertEquals(2, $child->id);
        $this->assertEquals(1, $child->root);
        $this->assertEquals(2, $child->lft);
        $this->assertEquals(3, $child->rgt);

        $root1->delete();

        $count = NestedModel::objects()->count();
        $this->assertEquals(0, $count);
    }

    public function testInit()
    {
        $model = new NestedModel;
        $fields = $model->getMeta()->getFields();
        $this->assertTrue(array_key_exists("id", $fields));
        $this->assertTrue(array_key_exists("name", $fields));
        $this->assertTrue(array_key_exists("lft", $fields));
        $this->assertTrue(array_key_exists("rgt", $fields));
        $this->assertTrue(array_key_exists("level", $fields));
        $this->assertTrue(array_key_exists("root", $fields));
        $this->assertTrue(array_key_exists("parent", $fields));
        $this->assertEquals(7, count($fields));
    }

    public function testTree()
    {
        $attrs = [
            ['name' => '1'],
            ['name' => '2'],
            ['name' => '3', 'parent' => 2],
            ['name' => '4', 'parent' => 2],
            ['name' => '5', 'parent' => 4]
        ];
        foreach ($attrs as $item) {
            $this->assertTrue((new NestedModel($item))->save());
        }

        $data = NestedModel::objects()->asTree()->all();
        $this->assertEquals([
            ['id' => '1', 'parent_id' => null, 'lft' => '1', 'rgt' => '2', 'level' => '1', 'root' => '1', 'name' => '1', 'items' => []],
            ['id' => '2', 'parent_id' => null, 'lft' => '1', 'rgt' => '8', 'level' => '1', 'root' => '2', 'name' => '2', 'items' => [
                ['id' => '3', 'parent_id' => '2', 'lft' => '2', 'rgt' => '3', 'level' => '2', 'root' => '2', 'name' => '3', 'items' => []],
                ['id' => '4', 'parent_id' => '2', 'lft' => '4', 'rgt' => '7', 'level' => '2', 'root' => '2', 'name' => '4', 'items' => [
                    ['id' => '5', 'parent_id' => '4', 'lft' => '5', 'rgt' => '6', 'level' => '3', 'root' => '2', 'name' => '5', 'items' => []]
                ]]
            ]]
        ], $data);
    }

    /**
     * https://github.com/studio107/Mindy_Orm/issues/50
     */
    public function testFixIsLeaf()
    {
        NestedModel::objects()->getOrCreate(['name' => 'root1']);
        $this->assertTrue(NestedModel::objects()->get(['name' => 'root1'])->getIsLeaf());

        NestedModel::objects()->getOrCreate(['name' => 'root2']);
        $this->assertTrue(NestedModel::objects()->get(['name' => 'root2'])->getIsLeaf());

        NestedModel::objects()->getOrCreate(['name' => 'nested', 'parent' => 2]);
        $this->assertTrue(NestedModel::objects()->get(['name' => 'root1'])->getIsLeaf());
        // root2 is leaf = false (nested)
        $this->assertFalse(NestedModel::objects()->get(['name' => 'root2'])->getIsLeaf());
        // nested is leaf
        $this->assertTrue(NestedModel::objects()->get(['name' => 'nested'])->getIsLeaf());

        // Remove nested from root2
        NestedModel::objects()->filter(['name' => 'nested'])->delete();
        // Root1 is leaf - true
        $this->assertTrue(NestedModel::objects()->get(['name' => 'root1'])->getIsLeaf());
        // Root2 is leaf - true
        $this->assertTrue(NestedModel::objects()->get(['name' => 'root2'])->getIsLeaf());
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
        NestedModel::objects()->filter(['name' => 'nested2'])->delete();
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
}
