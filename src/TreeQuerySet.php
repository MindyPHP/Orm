<?php

namespace Mindy\Orm;

use Doctrine\DBAL\Connection;
use Mindy\Interfaces\Arrayable;
use Mindy\QueryBuilder\Expression;
use Mindy\QueryBuilder\Q\QAndNot;
use Mindy\QueryBuilder\QueryBuilder;

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
        $this->asArray(true);

        $this->treeKey = $key;
        return $this->order(['root', 'lft']);
    }

    public function all()
    {
        $data = parent::all();
        return $this->treeKey ? $this->toHierarchy($data) : $data;
    }

    /**
     * Find broken branch with deleted roots
     * sql:
     * SELECT t.id FROM tbl t WHERE
     * t.parent_id IS NOT NULL AND t.root NOT IN (
     *      SELECT r.id FROM tbl r WHERE r.parent_id IS NULL
     * )
     *
     * Example: root1[1,4], nested1[2,3] and next delete root1 via QuerySet
     * like this: Model::objects()->filter(['name' => 'root1'])->delete();
     *
     * Problem: we have nested1 with lft 2 and rgt 3 without root.
     * Need find it and delete.
     */
    protected function deleteBranchWithoutRoot(Connection $db, $table)
    {
        $subQuery = clone $this->getQueryBuilder();
        $subQuery->clear()->setTypeSelect()->from($table)->select('root')->where(['parent_id__isnull' => true]);

        $query = clone $this->getQueryBuilder();
        $query->clear()->setTypeSelect()->select(['id'])->from($table)->where([
            'parent_id__isnull' => true,
            new QAndNot(['root__in' => $subQuery])
        ]);

        $ids = $db->query($query->toSQL())->fetchColumn();
        if ($ids && count($ids) > 0) {
            $deleteQuery = clone $this->getQueryBuilder();
            $deleteQuery->clear()->setTypeDelete()->from($table)->where(['id__in' => $ids]);
            $db->query($deleteQuery->toSQL())->execute();
        }
    }

    /**
     * Find broken branch with deleted parent
     * sql:
     * SELECT t.id, t.lft, t.rgt, t.root FROM tbl t
     * WHERE t.parent_id NOT IN (SELECT r.id FROM tbl r)
     *
     * Example: root1[1,6], nested1[2,5], nested2[3,4] and next delete nested1 via QuerySet
     * like this: Model::objects()->filter(['name' => 'nested1'])->delete();
     *
     * Problem: we have nested2 with lft 3 and rgt 4 without parent node.
     * Need find it and delete.
     */
    protected function deleteBranchWithoutParent(Connection $db, $table)
    {
        /*
        $query = new Query([
            'select' => ['id', 'lft', 'rgt', 'root'],
            'from' => $table,
            'where' => new Expression($db->quoteColumnName('parent_id') . ' NOT IN (' . $subQuery->allSql() . ')')
        ]);
         */
        $subQuery = clone $this->getQueryBuilder();
        $subQuery->clear()->setTypeSelect()->select(['id'])->from($table);

        $query = clone $this->getQueryBuilder();
        $query->clear()->setTypeSelect()->select(['id', 'lft', 'rgt', 'root'])->from($table)->where([
            new QAndNot(['parent_id__in' => $subQuery])
        ]);

        $rows = $db->query($query->toSQL())->fetchAll();
        foreach ($rows as $row) {
            $deleteQuery = clone $this->getQueryBuilder();
            $deleteQuery->clear()->setTypeDelete()->from($table)->where([
                'lft__gte' => $row['lft'],
                'rgt__lte' => $row['rgt'],
                'root' => $row['root']
            ]);
            $db->query($deleteQuery->toSQL())->execute();
        }
    }

    /*
     * Find and delete broken branches without root, parent
     * and with incorrect lft, rgt.
     *
     * sql:
     * SELECT id, root, lft, rgt, (rgt-lft-1) AS move
     * FROM tbl t
     * WHERE NOT t.lft = (t.rgt-1)
     * AND NOT id IN (
     *      SELECT tc.parent_id
     *      FROM tbl tc
     *      WHERE tc.parent_id = t.id
     * )
     * ORDER BY rgt DESC
     */
    protected function rebuildLftRgt(Connection $db, $table)
    {
        $subQuery = "SELECT [[tt]].[[parent_id]] FROM " . $table . " AS [[tt]] WHERE [[tt]].[[parent_id]]=[[t]].[[id]]";
        $where = 'NOT [[lft]]=([[rgt]]-1) AND NOT [[id]] IN (' . $subQuery . ')';
        $sql = "SELECT [[id]], [[root]], [[lft]], [[rgt]], [[rgt]]-[[lft]]-1 AS [[move]] FROM " . $table . " AS [[t]] WHERE " . $where . " ORDER BY [[rgt]] ASC";
        $adapter = QueryBuilder::getInstance($db)->getAdapter();

        $rows = $db->query($adapter->quoteSql($sql))->fetchAll();
        foreach ($rows as $row) {
            $sql = 'UPDATE ' . $table . ' SET [[lft]]=[[lft]]-' . $row['move'] . ', [[rgt]]=[[rgt]]-' . $row['move'] . ' WHERE [[root]]=' . $row['root'] . ' AND [[lft]]>' . $row['rgt'];
            $db->query($adapter->quoteSql($sql))->execute();
            $sql = 'UPDATE ' . $table . ' SET [[rgt]]=[[rgt]]-' . $row['move'] . ' WHERE [[root]]=' . $row['root'] . ' AND [[lft]]<[[rgt]] AND [[rgt]]>=' . $row['rgt'];
            $db->query($adapter->quoteSql($sql))->execute();
        }
    }

    /**
     * WARNING: Don't use QuerySet inside QuerySet in this
     * method because recursion...
     *
     * @throws \Mindy\Query\Exception
     */
    protected function findAndFixCorruptedTree()
    {
        $model = $this->model;
        $db = $model->getConnection();
        $table = $model->tableName();
        $this->deleteBranchWithoutRoot($db, $table);
        $this->deleteBranchWithoutParent($db, $table);
        $this->rebuildLftRgt($db, $table);
    }

    /**
     * Пересчитываем дерево после удаления моделей через
     * $modelClass::objects()->filter(['pk__in' => $data])->delete();
     * @return int
     */
    public function delete()
    {
        $deleted = parent::delete();
        $this->findAndFixCorruptedTree();
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
        $trees = [];
        if (count($collection) > 0) {
            // Node Stack. Used to help building the hierarchy
            $stack = [];
            foreach ($collection as $item) {
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
