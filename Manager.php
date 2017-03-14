<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

/**
 * Class Manager.
 */
class Manager extends ManyToManyManager
{
    /**
     * @param string|array $value
     *
     * @return $this
     */
    public function with($value)
    {
        $this->getQuerySet()->with($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function asArray($value = true)
    {
        $this->getQuerySet()->asArray($value);

        return $this;
    }

    /**
     * @param $columns
     * @param null $option
     *
     * @return \Mindy\Orm\Manager
     */
    public function select($columns, $option = null)
    {
        $this->getQuerySet()->select($columns, $option);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($conditions = [])
    {
        return $this->getQuerySet()->get($conditions);
    }

    /**
     * @param array $q
     *
     * @return string
     */
    public function getSql(array $q = [])
    {
        return $this->filter($q)->getQuerySet()->getSql();
    }

    /**
     * @param $rows
     *
     * @return Model[]
     */
    public function createModels($rows)
    {
        return $this->getQuerySet()->createModels($rows);
    }

    /**
     * @param bool $asArray
     *
     * @return string
     */
    public function allSql($asArray = false)
    {
        return $this->getQuerySet()->asArray($asArray)->allSql();
    }

    /**
     * @return mixed
     */
    public function countSql()
    {
        return $this->getQuerySet()->countSql();
    }

    /**
     * {@inheritdoc}
     */
    public function average($q)
    {
        return $this->getQuerySet()->average($q);
    }

    /**
     * @param $q
     *
     * @return string
     */
    public function averageSql($q)
    {
        return $this->getQuerySet()->averageSql($q);
    }

    /**
     * {@inheritdoc}
     */
    public function min($q)
    {
        return $this->getQuerySet()->min($q);
    }

    /**
     * @param $q
     *
     * @return string
     */
    public function minSql($q)
    {
        return $this->getQuerySet()->minSql($q);
    }

    /**
     * {@inheritdoc}
     */
    public function max($q)
    {
        return $this->getQuerySet()->max($q);
    }

    /**
     * @param $q
     *
     * @return string
     */
    public function maxSql($q)
    {
        return $this->getQuerySet()->maxSql($q);
    }

    /**
     * {@inheritdoc}
     */
    public function sum($q)
    {
        return $this->getQuerySet()->sum($q);
    }

    /**
     * @param $q
     *
     * @return string
     */
    public function sumSql($q)
    {
        return $this->getQuerySet()->sumSql($q);
    }

    /**
     * @param $q
     * @param bool $flat
     *
     * @return array
     */
    public function valuesList($q, $flat = false)
    {
        return $this->getQuerySet()->valuesList($q, $flat);
    }

    /**
     * Get model if exists. Else create model.
     *
     * @param array $attributes
     *
     * @return array
     */
    public function getOrCreate(array $attributes)
    {
        return $this->getQuerySet()->getOrCreate($attributes);
    }

    public function setSql($sql)
    {
        $this->getQuerySet()->setSql($sql);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function updateOrCreate(array $attributes, array $updateAttributes)
    {
        return $this->getQuerySet()->updateOrCreate($attributes, $updateAttributes);
    }

    public function update(array $attributes)
    {
        return $this->getQuerySet()->update($attributes);
    }

    public function updateSql(array $attributes)
    {
        return $this->getQuerySet()->updateSql($attributes);
    }

    public function delete(array $attributes = [])
    {
        return $this->filter($attributes)->getQuerySet()->delete();
    }

    public function deleteSql(array $attributes = [])
    {
        return $this->filter($attributes)->getQuerySet()->deleteSql();
    }

    public function create(array $attributes)
    {
        $model = $this->getModel();
        $model->setAttributes($attributes);

        return $model->save();
    }

    public function addGroupBy($column)
    {
        $this->getQuerySet()->addGroupBy($column);

        return $this;
    }

    public function truncate()
    {
        return $this->getQuerySet()->truncate();
    }

    /**
     * {@inheritdoc}
     */
    public function distinct($fields = true)
    {
        return $this->getQuerySet()->distinct($fields);
    }

    /**
     * {@inheritdoc}
     */
    public function group($fields)
    {
        $this->getQuerySet()->group($fields);

        return $this;
    }

    public function getTableAlias()
    {
        return $this->getQuerySet()->getTableAlias();
    }

    public function quoteColumnName($name)
    {
        return $this->getQuerySet()->quoteColumnName($name);
    }

    public function getQueryBuilder()
    {
        return $this->getQuerySet()->getQueryBuilder();
    }
}
