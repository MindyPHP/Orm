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
 * @date 17/04/14.04.2014 17:18
 */

namespace Mindy\Orm;

use Mindy\Helper\Interfaces\Arrayable;
use Mindy\Query\Expression;

class TreeQuerySet extends QuerySet
{
    protected $treeKey;

    /**
     * Named scope. Gets descendants for node.
     * @param bool $includeSelf
     * @param int $depth the depth.
     * @return QuerySet
     */
    public function descendants($includeSelf = false, $depth = null)
    {
        $this->filter([
            'lft__gt' => $this->model->lft,
            'rgt__lt' => $this->model->rgt,
            'root' => $this->model->root
        ])->order(['lft']);

        if ($includeSelf === false) {
            $this->exclude([
                'pk' => $this->model->pk
            ]);
        }

        if ($depth !== null) {
            $this->filter([
                'level__lte' => $this->model->level + $depth
            ]);
        }

        return $this;
    }

    /**
     * Named scope. Gets children for node (direct descendants only).
     * @param bool $includeSelf
     * @return QuerySet
     */
    public function children($includeSelf = false)
    {
        return $this->descendants($includeSelf, 1);
    }

    /**
     * Named scope. Gets ancestors for node.
     * @param int $depth the depth.
     * @return QuerySet
     */
    public function ancestors($depth = null)
    {
        $qs = $this->filter([
            'lft__lt' => $this->model->lft,
            'rgt__gt' => $this->model->rgt,
            'root' => $this->model->root
        ])->order(['lft']);

        if ($depth !== null) {
            $qs = $qs->filter(['level__gte' => $this->model->level + $depth]);
        }

        return $qs;
    }

    /**
     * Named scope. Gets root node(s).
     * @return QuerySet
     */
    public function roots()
    {
        return $this->filter(['lft' => 1]);
    }

    /**
     * Named scope. Gets parent of node.
     * @return QuerySet
     */
    public function parent()
    {
        return $this->filter([
            'lft__lt' => $this->model->lft,
            'rgt__gt' => $this->model->rgt,
            'level' => $this->model->level - 1,
            'root' => $this->model->root
        ]);
    }

    /**
     * Named scope. Gets previous sibling of node.
     * @return QuerySet
     */
    public function prev()
    {
        return $this->filter([
            'rgt' => $this->model->lft - 1,
            'root' => $this->model->root,
        ]);
    }

    /**
     * Named scope. Gets next sibling of node.
     * @return QuerySet
     */
    public function next()
    {
        return $this->filter([
            'lft' => $this->model->rgt + 1,
            'root' => $this->model->root,
        ]);
    }

    /**
     * @return int
     */
    protected function getLastRoot()
    {
        return ($max = $this->max('root')) ? $max + 1 : 1;
    }

    public function asTree($key = 'items')
    {
        $this->treeKey = $key;
        return $this;
    }

    public function all($db = null)
    {
        $data = parent::all($db);
        return $this->treeKey ? $this->toHierarchy($data) : $data;
    }

    /**
     * Пересчитываем дерево после удаления моделей через
     * $modelClass::objects()->filter(['pk__in' => $data])->delete();
     *
     * @param null $db
     * @return int
     */
    public function delete($db = null)
    {
        $pkList = $this->valuesList(['parent_id'], true);
        $deleted = parent::delete($db);
        if ($deleted && !empty($pkList)) {
            $pkList = array_unique(array_filter($pkList));
            $this->where = [];
            $this->filter(['pk__in' => $pkList])->update([
                'rgt' => new Expression('`lft`+1')
            ]);
        }
        return $deleted;
    }

    /**
     * Make hierarchy array by level
     * @param $collection Model[]
     * @return array
     */
    public function toHierarchy($collection)
    {
        // Trees mapped
        $trees = array();
        if (count($collection) > 0) {
            // Node Stack. Used to help building the hierarchy
            $stack = [];
            foreach ($collection as $item) {
                if ($item instanceof Arrayable) {
                    $item = $item->toArray();
                }
                $item[$this->treeKey] = [];
                // Number of stack items
                $l = count($stack);
                // Check if we're dealing with different levels
                while ($l > 0 && $stack[$l - 1]['level'] >= $item['level']) {
                    array_pop($stack);
                    $l--;
                }
                // Stack is empty (we are inspecting the root)
                if ($l == 0) {
                    // Assigning the root node
                    $i = count($trees);
                    $trees[$i] = $item;
                    $stack[] = & $trees[$i];
                } else {
                    // Add node to parent
                    $i = count($stack[$l - 1][$this->treeKey]);
                    $stack[$l - 1][$this->treeKey][$i] = $item;
                    $stack[] = & $stack[$l - 1][$this->treeKey][$i];
                }
            }
        }
        return $trees;
    }
}
