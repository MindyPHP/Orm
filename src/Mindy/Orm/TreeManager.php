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
 * @date 17/04/14.04.2014 14:21
 */

namespace Mindy\Orm;


class TreeManager extends Manager
{
    /**
     * Named scope. Gets descendants for node.
     * @param int $depth the depth.
     * @return QuerySet
     */
    public function descendants($depth = null)
    {
        $model = $this->getModel();

        $qs = $this->filter([
            'lft__gt' => $model->lft,
            'rgt__lt' => $model->rgt
        ])->order(['lft']);

        if ($depth !== null) {
            $qs = $qs->filter(['level__lte' => $model->level + $depth]);
        }

        return $qs->filter(['root' => $model->root]);
    }

    /**
     * Named scope. Gets children for node (direct descendants only).
     * @return QuerySet
     */
    public function children()
    {
        return $this->descendants(1);
    }

    /**
     * Named scope. Gets ancestors for node.
     * @param int $depth the depth.
     * @return QuerySet
     */
    public function ancestors($depth = null)
    {
        $model = $this->getModel();

        $qs = $this->filter([
            'lft__lt' => $model->lft,
            'rgt__gt' => $model->rgt
        ])->order(['lft']);

        if ($depth !== null) {
            $qs = $qs->filter(['level__gte' => $model->level + $depth]);
        }

        return $qs->filter(['root' => $model->root]);
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
        $model = $this->getModel();
        return $this->filter([
            'lft__lt' => $model->lft,
            'rgt__gt' => $model->rgt,
            'root' => $model->root
        ]);
    }

    /**
     * Named scope. Gets previous sibling of node.
     * @return QuerySet
     */
    public function prev()
    {
        $model = $this->getModel();
        return $this->filter([
            'rgt' => $model->lft - 1,
            'root' => $model->root,
        ]);
    }

    /**
     * Named scope. Gets next sibling of node.
     * @return QuerySet
     */
    public function next()
    {
        $model = $this->getModel();
        return $this->filter([
            'lft' => $model->rgt + 1,
            'root' => $model->root,
        ]);
    }

    /**
     * @return int
     */
    protected function getLastRoot()
    {
        $model = $this->getQuerySet()->max('root');
        return $model ? $model + 1 : 1;
    }
}
