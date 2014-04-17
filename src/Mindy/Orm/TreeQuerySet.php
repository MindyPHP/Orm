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

class TreeQuerySet extends QuerySet
{
    /**
     * Named scope. Gets descendants for node.
     * @param int $depth the depth.
     * @return QuerySet
     */
    public function descendants($depth = null)
    {
        $qs = $this->filter([
            'lft__gt' => $this->model->lft,
            'rgt__lt' => $this->model->rgt,
            'root' => $this->model->root
        ])->order(['lft']);

        if ($depth !== null) {
            $qs = $qs->filter(['level__lte' => $this->model->level + $depth]);
        }

        return $qs;
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
}
