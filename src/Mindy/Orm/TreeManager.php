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
     * @return \Mindy\Orm\TreeQuerySet
     */
    public function getQuerySet()
    {
        if ($this->_qs === null) {
            $this->_qs = new TreeQuerySet([
                'model' => $this->getModel(),
                'modelClass' => $this->getModel()->className()
            ]);
            $this->_qs->order(['lft']);
        }
        return $this->_qs;
    }

    /**
     * Named scope. Gets descendants for node.
     * @param int $depth the depth.
     * @return QuerySet
     */
    public function descendants($includeSelf = false, $depth = null)
    {
        $this->getQuerySet()->descendants($includeSelf, $depth);
        return $this;
    }

    /**
     * Named scope. Gets children for node (direct descendants only).
     * @return QuerySet
     */
    public function children($includeSelf = false)
    {
        $this->getQuerySet()->children($includeSelf);
        return $this;
    }

    /**
     * Named scope. Gets ancestors for node.
     * @param int $depth the depth.
     * @return QuerySet
     */
    public function ancestors($depth = null)
    {
        $this->getQuerySet()->ancestors($depth);
        return $this;
    }

    /**
     * Named scope. Gets root node(s).
     * @return QuerySet
     */
    public function roots()
    {
        $this->getQuerySet()->roots();
        return $this;
    }

    /**
     * Named scope. Gets parent of node.
     * @return QuerySet
     */
    public function parent()
    {
        $this->getQuerySet()->parent();
        return $this;
    }

    /**
     * Named scope. Gets previous sibling of node.
     * @return QuerySet
     */
    public function prev()
    {
        $this->getQuerySet()->prev();
        return $this;
    }

    /**
     * Named scope. Gets next sibling of node.
     * @return QuerySet
     */
    public function next()
    {
        $this->getQuerySet()->next();
        return $this;
    }

    public function asTree($key = 'items')
    {
        $this->getQuerySet()->asTree($key);
        return $this;
    }
}
