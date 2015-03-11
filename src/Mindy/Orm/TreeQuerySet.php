<?php

namespace Mindy\Orm;

use Mindy\Helper\Interfaces\Arrayable;
use Mindy\Query\Expression;

/**
 * Class TreeQuerySet
 * @package Mindy\Orm
 */
class TreeQuerySet extends QuerySet
{
    protected $treeKey;

    /**
     * TODO переписать логику на $includeSelf = true делать gte, lte иначе gt, lt соответственно
     * Named scope. Gets descendants for node.
     * @param bool $includeSelf
     * @param int $depth the depth.
     * @return QuerySet
     */
    public function descendants($includeSelf = false, $depth = null)
    {
        $this->filter([
            'lft__gte' => $this->model->lft,
            'rgt__lte' => $this->model->rgt,
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
     * @param bool $includeSelf
     * @param int $depth the depth.
     * @return QuerySet
     */
    public function ancestors($includeSelf = false, $depth = null)
    {
        $qs = $this->filter([
            'lft__lte' => $this->model->lft,
            'rgt__gte' => $this->model->rgt,
            'root' => $this->model->root
        ])->order(['-lft']);

        if ($includeSelf === false) {
            $this->exclude([
                'pk' => $this->model->pk
            ]);
        }

        if ($depth !== null) {
            $qs = $qs->filter(['level__lte' => $this->model->level - $depth]);
        }

        return $qs;
    }

    /**
     * @param bool $includeSelf
     * @return QuerySet
     */
    public function parents($includeSelf = false)
    {
        return $this->ancestors($includeSelf, 1);
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
        return $this->order(['root', 'lft']);
    }

    public function all($db = null)
    {
        $data = parent::all($db);
        return $this->treeKey ? $this->toHierarchy($data) : $data;
    }

    protected function prepareProblemLftRgt()
    {
        /*
        select id, root, lft, rgt, (rgt-lft) as move
        from {{table_name}} t
        where not t.lft = (t.rgt-1)
        and not id in ( select tc.parent_id from {{table_name}} tc where tc.parent_id = t.id );
         */

        /*
        $qsRange = clone $this;
        $alias = $qsRange->makeAliasKey($this->model->tableName(), false);
        $qsRange->clearFilter();
        $qsRange->orderBy = null;
        $qsRange->select([
            $alias . '.id',
            $alias . '.root',
            $alias . '.lft',
            $alias . '.rgt',
            new Expression('(' . $this->quoteColumnName($alias . '.rgt') . '-' . $this->quoteColumnName($alias . '.lft') . ') as move')
        ]);
        $qsRange->exclude([
            'lft' => new Expression($this->quoteColumnName($alias . '.rgt') . '-1'),
        ]);

        $qs = clone $this;
        $qs->clearFilter();
        $qs->orderBy = null;
        $qs->exclude([
            'parent_id__in' => $qsRange->valuesList(['id'], true)
        ]);
        d($qs->update(['']));
        */

        $elements = $this->createCommand()->setSql("
        select id, root, lft, rgt, (rgt-lft-1) as move
        from {$this->model->tableName()} t
        where not t.lft = (t.rgt-1)
        and not id in ( select tc.parent_id from {$this->model->tableName()} tc where tc.parent_id = t.id )
        ORDER BY rgt DESC
        ")->queryAll();

        foreach ($elements as $element) {
            $sql = "
            update {$this->model->tableName()}
            set lft = lft - {$element['move']}, rgt = rgt - {$element['move']}
            where root = {$element['root']}
              and lft > {$element['rgt']};
            ";
            $this->createCommand()->setSql($sql)->execute();
            $sql = "
            update {$this->model->tableName()}
            set rgt = rgt - {$element['move']}
            where root = {$element['root']}
              and lft <  {$element['rgt']}
              and rgt >= {$element['rgt']}
            ";
            $this->createCommand()->setSql($sql)->execute();
        }
    }

    /**
     * Пересчитываем дерево после удаления моделей через
     * $modelClass::objects()->filter(['pk__in' => $data])->delete();
     * @return int
     */
    public function delete()
    {
        $deleted = parent::delete();

        $this->prepareProblemLftRgt();

        return $deleted;
    }

    /**
     * @param int $key .
     * @param int $delta .
     * @param int $root .
     * @param array $data .
     * @return array
     */
    private function shiftLeftRight($key, $delta, $root, $data)
    {
        foreach (['lft', 'rgt'] as $attribute) {
            $this->filter([$attribute . '__gte' => $key, 'root' => $root])
                ->update([$attribute => new Expression($attribute . sprintf('%+d', $delta))]);

            foreach ($data as &$item) {
                if ($item[$attribute] >= $key) {
                    $item[$attribute] += $delta;
                }
            }
        }
        return $data;
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
                    $stack[] = &$trees[$i];
                } else {
                    // Add node to parent
                    $i = count($stack[$l - 1][$this->treeKey]);
                    $stack[$l - 1][$this->treeKey][$i] = $item;
                    $stack[] = &$stack[$l - 1][$this->treeKey][$i];
                }
            }
        }
        return $trees;
    }
}
