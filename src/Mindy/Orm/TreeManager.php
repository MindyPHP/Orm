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
        return $this->getQuerySet()->descendants($includeSelf, $depth);
    }

    /**
     * Named scope. Gets children for node (direct descendants only).
     * @return QuerySet
     */
    public function children($includeSelf = false)
    {
        return $this->getQuerySet()->children($includeSelf);
    }

    /**
     * Named scope. Gets ancestors for node.
     * @param int $depth the depth.
     * @return QuerySet
     */
    public function ancestors($depth = null)
    {
        return $this->getQuerySet()->ancestors($depth);
    }

    /**
     * Named scope. Gets root node(s).
     * @return QuerySet
     */
    public function roots()
    {
        return $this->getQuerySet()->roots();
    }

    /**
     * Named scope. Gets parent of node.
     * @return QuerySet
     */
    public function parent()
    {
        return $this->getQuerySet()->parent();
    }

    /**
     * Named scope. Gets previous sibling of node.
     * @return QuerySet
     */
    public function prev()
    {
        return $this->getQuerySet()->prev();
    }

    /**
     * Named scope. Gets next sibling of node.
     * @return QuerySet
     */
    public function next()
    {
        return $this->getQuerySet()->next();
    }

    public function asTree($key = 'items')
    {
        return $this->getQuerySet()->asTree($key);
    }
}
