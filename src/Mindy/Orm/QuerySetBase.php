<?php

namespace Mindy\Orm;

use ArrayAccess;
use Exception;
use IteratorAggregate;
use Mindy\Base\Mindy;
use Mindy\Helper\Creator;
use Mindy\Query\Query;
use Serializable;

/**
 * Class QuerySetBase
 * @package Mindy\Orm
 */
abstract class QuerySetBase extends Query implements IteratorAggregate, ArrayAccess, Serializable
{
    /**
     * @var string the name of the ActiveRecord class.
     */
    public $modelClass;
    /**
     * @var boolean whether to return each record as an array. If false (default), an object
     * of [[modelClass]] will be created to represent each record.
     */
    protected $asArray;
    /**
     * @var boolean whether to return statement as an sql.
     */
    protected $asSql;
    /**
     * @var DataIterator
     */
    private $_iterator;

    abstract public function getData();

    protected function getEventManager()
    {
        return Mindy::app()->getComponent('signal');
    }

    /**
     * Sets the [[asArray]] property.
     * @param boolean $value whether to return the query results in terms of arrays instead of Active Records.
     * @return static the query object itself
     */
    public function asArray($value = true)
    {
        $this->asArray = $value;
        return $this;
    }

    /**
     * Sets the [[asArray]] property.
     * @param boolean $value whether to return the query results in terms of arrays instead of Active Records.
     * @return static the query object itself
     */
    public function asSql($value = true)
    {
        $this->asSql = $value;
        return $this;
    }

    /**
     * @param $row array
     * @return Model
     */
    public function createModel(array $row)
    {
        /** @var Base $className */
        $className = $this->modelClass;
        if (!$className) {
            throw new Exception('$className must be a string in createModel method of qs');
        }
        return $className::create($row);
    }

    /**
     * Converts found rows into model instances
     * @param array $rows
     * @return array|Orm[]
     */
    public function createModels($rows)
    {
        $models = [];
        foreach ($rows as $row) {
            $models[] = $this->createModel($row);
        }
        return $models;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>\Traversable</b>
     */
    public function getIterator()
    {
        if (!$this->_iterator) {
            $this->_iterator = new DataIterator($this->all(), [
                'asArray' => $this->asArray,
                'qs' => $this
            ]);
        }
        return $this->_iterator;
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
        return $this->getIterator()->offsetExists($offset);
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
        return $this->getIterator()->offsetGet($offset);
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
        $this->getIterator()->offsetSet($offset, $value);
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
        $this->getIterator()->offsetUnset($offset);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        $props = Creator::getObjectVars($this);
        return serialize($props);
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
    public function unserialize($data)
    {
        $props = unserialize($data);
        Creator::configure($this, $props);
    }
}