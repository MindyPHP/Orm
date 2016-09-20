<?php

namespace Mindy\Orm;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Mindy\QueryBuilder\Expression;
use Serializable;
use Traversable;

/**
 * Class Manager
 * @package Mindy\Orm
 */
class Manager extends ManyToManyManager implements IteratorAggregate, ArrayAccess, ManagerInterface
{
    /**
     * @param string|array $value
     * @return $this
     */
    public function with($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        $this->getQuerySet()->with($value);
        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function asArray($value = true)
    {
        $this->getQuerySet()->asArray($value);
        return $this;
    }

    /**
     * @param string $connection
     * @return $this
     */
    public function using(string $connection)
    {
        $this->getQuerySet()->using($connection);
        return $this;
    }

    /**
     * @param $conditions
     * @return Manager|ManagerInterface
     */
    public function filter($conditions) : ManagerInterface
    {
        $this->getQuerySet()->filter($conditions);
        return $this;
    }

    /**
     * @param array $q
     * @return \Mindy\Orm\Manager
     */
    public function orFilter(array $q)
    {
        $this->getQuerySet()->orFilter($q);
        return $this;
    }

    /**
     * @param array $q
     * @return \Mindy\Orm\Manager
     */
    public function orExclude(array $q)
    {
        $this->getQuerySet()->orExclude($q);
        return $this;
    }

    /**
     * @param $columns
     * @param null $option
     * @return \Mindy\Orm\Manager
     */
    public function select($columns, $option = null)
    {
        $this->getQuerySet()->select($columns, $option);
        return $this;
    }

    /**
     * @param array $q
     * @return \Mindy\Orm\Manager
     */
    public function exclude(array $q)
    {
        $this->getQuerySet()->exclude($q);
        return $this;
    }

    /**
     * @param $conditions
     * @return ModelInterface|array|null
     */
    public function get($conditions = [])
    {
        $this->filter($conditions);
        return $this->getQuerySet()->get();
    }

    /**
     * @param array $q
     * @return string
     */
    public function getSql(array $q = [])
    {
        $this->filter($q);
        return $this->getQuerySet()->getSql();
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->getQuerySet()->all();
    }

    /**
     * @param int $batchSize
     * @return \Mindy\Orm\BatchDataIterator
     */
    public function batch($batchSize = 100)
    {
        return $this->getQuerySet()->batch($batchSize);
    }

    /**
     * @param int $batchSize
     * @return \Mindy\Orm\BatchDataIterator
     */
    public function each($batchSize = 100)
    {
        return $this->getQuerySet()->each($batchSize);
    }

    /**
     * @param string $q
     * @return string|int|float
     */
    public function count($q = '*')
    {
        return $this->getQuerySet()->count($q);
    }

    /**
     * @param $rows
     * @return Model[]
     */
    public function createModels($rows)
    {
        return $this->getQuerySet()->createModels($rows);
    }
    
    /**
     * @param bool $asArray
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
     * @param $columns
     * @return $this
     */
    public function order($columns)
    {
        $this->getQuerySet()->order($columns);
        return $this;
    }

    /**
     * @param $page
     * @param int $pageSize
     * @return array
     */
    public function paginate($page, $pageSize = 10)
    {
        $this->getQuerySet()->paginate($page, $pageSize);
        return $this;
    }

    /**
     * @param $limit
     * @return static
     */
    public function limit($limit)
    {
        $this->getQuerySet()->limit($limit);
        return $this;
    }

    /**
     * @param $offset int
     * @return static
     */
    public function offset($offset)
    {
        $this->getQuerySet()->offset($offset);
        return $this;
    }

    /**
     * @param $q
     * @return int
     */
    public function average($q)
    {
        return $this->getQuerySet()->average($q);
    }

    /**
     * @param $q
     * @return int
     */
    public function averageSql($q)
    {
        return $this->getQuerySet()->averageSql($q);
    }

    /**
     * @param $q
     * @return int
     */
    public function min($q)
    {
        return $this->getQuerySet()->min($q);
    }

    /**
     * @param $q
     * @return int
     */
    public function minSql($q)
    {
        return $this->getQuerySet()->minSql($q);
    }

    /**
     * @param $q
     * @return int
     */
    public function max($q)
    {
        return $this->getQuerySet()->max($q);
    }

    /**
     * @param $q
     * @return int
     */
    public function maxSql($q)
    {
        return $this->getQuerySet()->maxSql($q);
    }

    /**
     * @param $q
     * @return int
     */
    public function sum($q)
    {
        return $this->getQuerySet()->sum($q);
    }

    /**
     * @param $q
     * @return int
     */
    public function sumSql($q)
    {
        return $this->getQuerySet()->sumSql($q);
    }

    /**
     * @param $q
     * @param bool $flat
     * @return array
     */
    public function valuesList($q, $flat = false)
    {
        return $this->getQuerySet()->valuesList($q, $flat);
    }

    /**
     * Get model if exists. Else create model.
     * @param array $attributes
     * @return array
     */
    public function getOrCreate(array $attributes) : array
    {
        return $this->getQuerySet()->getOrCreate($attributes);
    }

    /**
     * Find and update model if exists. Else create model.
     * @param array $attributes attributes for query
     * @param array $updateAttributes attributes for update|create
     * @return Orm
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

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return $this->getQuerySet()->getIterator();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->getQuerySet()->offsetExists($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getQuerySet()->offsetGet($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->getQuerySet()->offsetSet($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->getQuerySet()->offsetUnset($offset);
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

    public function distinct($fields = true)
    {
        return $this->getQuerySet()->distinct($fields);
    }

    public function group($fields)
    {
        return $this->getQuerySet()->group($fields);
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
