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
 * @date 17/04/14.04.2014 14:15
 */

namespace Mindy\Orm;


use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\IntField;

/**
 * Class TreeModel
 * @method static \Mindy\Orm\TreeManager tree($instance = null)
 * @package Mindy\Orm
 */
abstract class TreeModel extends Model
{
    public function getFields()
    {
        return [
            'parent' => [
                'class' => ForeignField::className(),
                'modelClass' => get_class($this),
                'null' => true
            ],
            'lft' => [
                'class' => IntField::className()
            ],
            'rgt' => [
                'class' => IntField::className()
            ],
            'level' => [
                'class' => IntField::className()
            ],
            'root' => [
                'class' => IntField::className(),
                'null' => true
            ],
        ];
    }

    /**
     * @return TreeManager
     */
    public static function treeManager($instance = null)
    {
        $className = get_called_class();
        return new TreeManager($instance ? $instance : new $className);
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

    public function save(array $fields = [])
    {
        if($this->getIsNewRecord()) {
            if($this->parent === null) {
                $rgt = $this->objects()->max('rgt');
                $this->lft = $rgt ? $rgt + 1 : 1;
                $this->rgt = $this->lft + 1;

                $this->level = 0;
                $this->root = $this->objects()->max('root') + 1;
            } else {
                // Force get parent model
                $parent = $this->objects()->get(['pk' => $this->parent->pk]);

                $this->level = $parent->level + 1;
                $this->root = $parent->root;

                $this->lft = $parent->rgt;
                $this->rgt = $this->lft + 1;

                $this->objects()
                    ->filter(['root' => $parent->root, 'rgt__gte' => $this->lft])
                    ->updateCounters(['rgt' => 2]);
            }
        } else {
            // TODO
            if($this->getIsRoot()) {

            } else if($this->getIsLeaf()) {

            } else {

            }
        }
        return parent::save($fields);
    }
}
