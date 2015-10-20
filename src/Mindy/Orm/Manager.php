<?php

namespace Mindy\Orm;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Serializable;
use Traversable;

/**
 * Class Manager
 * @package Mindy\Orm
 */
class Manager implements IteratorAggregate, Serializable, Countable, ArrayAccess
{
    /**
     * @var \Mindy\Orm\Model
     */
    protected $_model;

    /**
     * @var \Mindy\Orm\QuerySet
     */
    protected $_qs;

    public function __construct(Model $model)
    {
        $this->_model = $model;
    }

    public function with(array $value)
    {
        $this->getQuerySet()->with($value);
        return $this;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @return \Mindy\Orm\QuerySet
     */
    public function getQuerySet()
    {
        if ($this->_qs === null) {
            $this->_qs = new QuerySet([
                'model' => $this->getModel(),
                'modelClass' => $this->getModel()->className()
            ]);
        }
        return $this->_qs;
    }

    public function asArray($value = true)
    {
        return $this->getQuerySet()->asArray($value);
    }

    public function asSql($value = true)
    {
        return $this->getQuerySet()->asSql($value);
    }

    public function using($db)
    {
        $this->getQuerySet()->using($db);
        return $this;
    }

    /**
     * Returns the primary key name(s) for this AR class.
     * The default implementation will return the primary key(s) as declared
     * in the DB table that is associated with this AR class.
     *
     * If the DB table does not declare any primary key, you should override
     * this method to return the attributes that you want to use as primary keys
     * for this AR class.
     *
     * Note that an array should be returned even for a table with single primary key.
     *
     * @return string[] the primary keys of the associated database table.
     */
    public function primaryKey()
    {
        return $this->getModel()->getTableSchema()->primaryKey;
    }

    /**
     * @param array $q
     * @return \Mindy\Orm\Manager
     */
    public function filter(array $q)
    {
        $this->getQuerySet()->filter($q);
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
     * @param array $q
     * @return TreeModel|Model|null
     */
    public function get(array $q = [])
    {
        $this->filter($q);
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
     * @return mixed
     */
    public function count()
    {
        return $this->getQuerySet()->count();
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
     * @return \Mindy\Orm\QuerySet
     */
    public function order(array $columns)
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
     * @return Orm
     */
    public function getOrCreate(array $attributes)
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

    public function updateCounters(array $counters)
    {
        return $this->getQuerySet()->updateCounters($counters);
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
        return $this->getModel()->setAttributes($attributes)->save();
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
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return $this->getQuerySet()->serialize();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return Model[]
     */
    public function unserialize($serialized)
    {
        return $this->getQuerySet()->unserialize($serialized);
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

    public function cache($duration = null, $dependency = null)
    {
        $this->getQuerySet()->cache($duration, $dependency);
        return $this;
    }

    public function noCache()
    {
        $this->getQuerySet()->noCache();
        return $this;
    }

    public function join($type, $table, $on = '', $params = [])
    {
        $this->getQuerySet()->join($type, $table, $on, $params);
        return $this;
    }

    public function from($tables)
    {
        $this->getQuerySet()->from($tables);
        return $this;
    }
}
