<?php

namespace Mindy\Orm;

use Exception;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Fields\TreeForeignField;
use Mindy\QueryBuilder\Expression;

/**
 * Class TreeModel
 * @method static \Mindy\Orm\TreeManager objects($instance = null)
 * @package Mindy\Orm
 */
abstract class TreeModel extends Model
{
    public static function getFields()
    {
        $parent = 'Parent';
        if (class_exists('\Mindy\Base\Mindy') && \Mindy\Base\Mindy::app()) {
            if (\Mindy\Base\Mindy::app()->hasModule(self::getModuleName())) {
                $module = \Mindy\Base\Mindy::app()->getModule(self::getModuleName());
                $parent = $module->t('Parent');
            }
        }
        return [
            'parent' => [
                'class' => TreeForeignField::className(),
                'modelClass' => get_called_class(),
                'null' => true,
                'verboseName' => $parent
            ],
            'lft' => [
                'class' => IntField::className(),
                'editable' => false,
                'null' => true
            ],
            'rgt' => [
                'class' => IntField::className(),
                'editable' => false,
                'null' => true
            ],
            'level' => [
                'class' => IntField::className(),
                'editable' => false,
                'null' => true
            ],
            'root' => [
                'class' => IntField::className(),
                'null' => true,
                'editable' => false
            ],
        ];
    }

    /**
     * @param null $instance
     * @return TreeManager
     */
    public static function objectsManager($instance = null)
    {
        $className = get_called_class();
        return new TreeManager($instance ? $instance : new $className);
    }

    /**
     * @DEPRECATED
     * @param null $instance
     * @return TreeManager
     */
    public static function treeManager($instance = null)
    {
        return self::objectsManager($instance);
    }

    /**
     * Determines if node is leaf.
     * @return boolean whether the node is leaf.
     */
    public function getIsLeaf()
    {
        return $this->rgt - $this->lft === 1;
    }

    /**
     * Determines if node is root.
     * @return boolean whether the node is root.
     */
    public function getIsRoot()
    {
        return $this->lft == 1;
    }

    /**
     * @var bool
     */
    private $_deleted = false;

    /**
     * Create root node if multiple-root tree mode. Update node if it's not new.
     * @param array $fields
     * @throws \Exception
     * @return boolean whether the saving succeeds.
     */
    public function save(array $fields = [])
    {
        if ($this->getIsNewRecord()) {
            if ($this->parent) {
                $this->appendTo($this->parent);
            } else {
                $this->makeRoot();
            }
        } else {
            $dirtyFields = $this->getDirtyAttributes();
            if (array_key_exists('parent_id', $dirtyFields)) {
                $saved = parent::save($fields);
                if ($this->parent) {
                    $this->moveAsLast($this->parent);
                } else if ($this->isRoot() == false) {
                    $this->moveAsRoot();
                }
                /** @var array $parent */
                $parent = $this->objects()->asArray()->filter(['pk' => $this->pk])->get();
                if ($parent !== null) {
                    $this->setAttributes($parent);
                    $this->setIsNewRecord(false);
                }
                return $saved;
            }
        }

        return parent::save($fields);
    }

    public function saveRebuild()
    {
        $fields = ['lft', 'rgt', 'level', 'root'];

        if ($this->parent == null) {
            $this->lft = 1;
            $this->rgt = 2;
            $this->level = 0;
            $this->root = $this->pk;
            return parent::save($fields);
        } else if ($this->parent->lft) {
            $target = $this->parent;
            $key = $target->rgt;

            $this->root = $target->root;

            $this->shiftLeftRight($key, 2);
            $this->lft = $key;
            $this->rgt = $key + 1;
            $this->level = $target->level + 1;

            return parent::save($fields);
        }
        return false;
    }

    /**
     * Deletes node and it's descendants.
     * @throws \Exception.
     * @return boolean whether the deletion is successful.
     */
    public function delete()
    {
        if ($this->isLeaf()) {
            $result = parent::delete();
        } else {
            $result = $this->objects()->filter([
                'lft__gte' => $this->lft,
                'rgt__lte' => $this->rgt,
                'root' => $this->root
            ])->delete();
        }
        return (bool)$result;
    }

    /**
     * Prepends node to target as first child.
     * @param TreeModel $target the target.
     * @return boolean whether the prepending succeeds.
     */
    public function prependTo($target)
    {
        return $this->addNode($target, $target->lft + 1, 1);
    }

    /**
     * Prepends target to node as first child.
     * @param TreeModel $target the target.
     * @return boolean whether the prepending succeeds.
     */
    public function prepend($target)
    {
        return $target->prependTo($this->owner);
    }

    /**
     * Appends node to target as last child.
     * @param TreeModel $target the target.
     * @return boolean whether the appending succeeds.
     */
    public function appendTo($target)
    {
        return $this->addNode($target, $target->rgt, 1);
    }

    /**
     * Appends target to node as last child.
     * @param TreeModel $target the target.
     * @return boolean whether the appending succeeds.
     */
    public function append($target)
    {
        return $target->appendTo($this->owner);
    }

    /**
     * Inserts node as previous sibling of target.
     * @param TreeModel $target the target.
     * @throws \Exception
     * @return boolean whether the inserting succeeds.
     */
    public function insertBefore($target)
    {
        return $this->addNode($target, $target->lft, 0);
    }

    /**
     * Inserts node as next sibling of target.
     * @param TreeModel $target the target.
     * @return boolean whether the inserting succeeds.
     */
    public function insertAfter($target)
    {
        return $this->addNode($target, $target->rgt + 1, 0);
    }

    /**
     * Move node as previous sibling of target.
     * @param TreeModel $target the target.
     * @return boolean whether the moving succeeds.
     */
    public function moveBefore($target)
    {
        return $this->moveNode($target, $target->lft, 0);
    }

    /**
     * Move node as next sibling of target.
     * @param TreeModel $target the target.
     * @return boolean whether the moving succeeds.
     */
    public function moveAfter($target)
    {
        return $this->moveNode($target, $target->rgt + 1, 0);
    }

    /**
     * Move node as first child of target.
     * @param TreeModel $target the target.
     * @return boolean whether the moving succeeds.
     */
    public function moveAsFirst($target)
    {
        return $this->moveNode($target, $target->lft + 1, 1);
    }

    /**
     * Move node as last child of target.
     * @param TreeModel $target the target.
     * @return boolean whether the moving succeeds.
     */
    public function moveAsLast($target)
    {
        return $this->moveNode($target, $target->rgt, 1);
    }

    /**
     * Move node as new root.
     * @throws Exception.
     * @throws \Exception.
     * @return boolean whether the moving succeeds.
     */
    public function moveAsRoot()
    {
        if ($this->getIsNewRecord()) {
            throw new Exception('The node should not be new record.');
        }

        if ($this->getIsDeletedRecord()) {
            throw new Exception('The node should not be deleted.');
        }

        if ($this->isRoot()) {
            throw new Exception('The node already is root node.');
        }

        $left = $this->lft;
        $right = $this->rgt;
        $levelDelta = 1 - $this->level;
        $delta = 1 - $left;
        $this->objects()
            ->filter([
                'lft__gte' => $left,
                'rgt__lte' => $right,
                'root' => $this->root
            ])
            ->update([
                'lft' => new Expression('lft' . sprintf('%+d', $delta)),
                'rgt' => new Expression('rgt' . sprintf('%+d', $delta)),
                'level' => new Expression('level' . sprintf('%+d', $levelDelta)),
                'root' => $this->getMaxRoot()
            ]);
        $this->shiftLeftRight($right + 1, $left - $right - 1);

        return true;
    }

    /**
     * Determines if node is descendant of subject node.
     * @param TreeModel $subj the subject node.
     * @return boolean whether the node is descendant of subject node.
     */
    public function isDescendantOf($subj)
    {
        return ($this->lft > $subj->lft) && ($this->rgt < $subj->rgt) && ($this->root === $subj->root);
    }

    /**
     * Determines if node is leaf.
     * @return boolean whether the node is leaf.
     */
    public function isLeaf()
    {
        return $this->rgt - $this->lft === 1;
    }

    /**
     * Determines if node is root.
     * @return boolean whether the node is root.
     */
    public function isRoot()
    {
        return $this->getIsRoot();
    }

    /**
     * Returns if the current node is deleted.
     * @return boolean whether the node is deleted.
     */
    public function getIsDeletedRecord()
    {
        return $this->_deleted;
    }

    /**
     * Sets if the current node is deleted.
     * @param boolean $value whether the node is deleted.
     */
    public function setIsDeletedRecord($value)
    {
        $this->_deleted = $value;
    }

    /**
     * @param int $key .
     * @param int $delta .
     */
    private function shiftLeftRight($key, $delta)
    {
        foreach (['lft', 'rgt'] as $attribute) {
            $this->objects()
                ->filter([$attribute . '__gte' => $key, 'root' => $this->root])
                ->update([$attribute => new Expression($attribute . sprintf('%+d', $delta))]);
        }
    }

    /**
     * @param TreeModel $target .
     * @param int $key .
     * @param int $levelUp .
     * @throws \Exception .
     * @return boolean.
     */
    private function addNode($target, $key, $levelUp)
    {
        if (!$this->getIsNewRecord()) {
            throw new Exception('The node can\'t be inserted because it is not new.');
        }

        if ($this->getIsDeletedRecord()) {
            throw new Exception('The node can\'t be inserted because it is deleted.');
        }

        if ($target->getIsDeletedRecord()) {
            throw new Exception('The node can\'t be inserted because target node is deleted.');
        }

        if ($this->pk == $target->pk) {
            throw new Exception('The target node should not be self.');
        }

        if (!$levelUp && $target->isRoot()) {
            throw new Exception('The target node should not be root.');
        }

        $this->root = $target->root;

        $this->shiftLeftRight($key, 2);
        $this->lft = $key;
        $this->rgt = $key + 1;
        $this->level = $target->level + $levelUp;
        return $this;
    }

    private function getMaxRoot()
    {
        return $this->objects()->max('root') + 1;
    }

    /**
     * @throws \Exception.
     * @return boolean.
     */
    private function makeRoot()
    {
        $this->lft = 1;
        $this->rgt = 2;
        $this->level = 1;
        $this->root = $this->getMaxRoot();
        return $this;
    }

    /**
     * @param TreeModel $target .
     * @param int $key .
     * @param int $levelUp .
     * @throws Exception.
     * @throws \Exception.
     * @return boolean.
     */
    private function moveNode($target, $key, $levelUp)
    {
        if ($this->getIsNewRecord()) {
            throw new Exception('The node should not be new record.');
        }

        if ($this->getIsDeletedRecord()) {
            throw new Exception('The node should not be deleted.');
        }

        if ($target->getIsDeletedRecord()) {
            throw new Exception('The target node should not be deleted.');
        }

        if ($this->pk == $target->pk) {
            throw new Exception('The target node should not be self.');
        }

        if ($target->isDescendantOf($this)) {
            throw new Exception('The target node should not be descendant.');
        }

        if (!$levelUp && $target->isRoot()) {
            throw new Exception('The target node should not be root.');
        }

        $left = $this->lft;
        $right = $this->rgt;
        $levelDelta = $target->level - $this->level + $levelUp;

        if ($this->root !== $target->root) {
            foreach (['lft', 'rgt'] as $attribute) {
                $this->objects()
                    ->filter([$attribute . '__gte' => $key, 'root' => $target->root])
                    ->update([$attribute => new Expression($attribute . sprintf('%+d', $right - $left + 1))]);
            }

            $delta = $key - $left;
            $this->objects()
                ->filter(['lft__gte' => $left, 'rgt__lte' => $right, 'root' => $this->root])
                ->update([
                    'lft' => new Expression('lft' . sprintf('%+d', $delta)),
                    'rgt' => new Expression('rgt' . sprintf('%+d', $delta)),
                    'level' => new Expression('level' . sprintf('%+d', $levelDelta)),
                    'root' => $target->root,
                ]);

            $this->shiftLeftRight($right + 1, $left - $right - 1);

        } else {
            $delta = $right - $left + 1;
            $this->shiftLeftRight($key, $delta);

            if ($left >= $key) {
                $left += $delta;
                $right += $delta;
            }

            $this->objects()
                ->filter(['lft__gte' => $left, 'rgt__lte' => $right, 'root' => $this->root])
                ->update([
                    'level' => new Expression('level' . sprintf('%+d', $levelDelta))
                ]);

            foreach (['lft', 'rgt'] as $attribute) {
                $this->objects()
                    ->filter([$attribute . '__gte' => $left, $attribute . '__lte' => $right, 'root' => $this->root])
                    ->update([$attribute => new Expression($attribute . sprintf('%+d', $key - $left))]);
            }

            $this->shiftLeftRight($right + 1, -$delta);
        }
        return true;
    }
}
