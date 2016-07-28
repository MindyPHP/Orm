<?php

namespace Mindy\Orm;

use ArrayAccess;
use Exception;
use IteratorAggregate;
use Mindy\Base\Mindy;
use Mindy\Helper\Creator;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;
use Mindy\Orm\Callback\FetchColumnCallback;
use Mindy\Orm\Callback\JoinCallback;
use Mindy\Orm\Callback\LookupCallback;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\OneToOneField;
use Mindy\Orm\Fields\RelatedField;
use Mindy\Query\Connection;
use Mindy\QueryBuilder\LookupBuilder\Legacy;
use Mindy\QueryBuilder\QueryBuilder;
use Serializable;

/**
 * Class QuerySetBase
 * @package Mindy\Orm
 */
abstract class QuerySetBase implements IteratorAggregate, ArrayAccess, Serializable
{
    use Accessors, Configurator;

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
    /**
     * @var \Mindy\Query\Connection
     */
    private $_db;
    /**
     * @var \Mindy\QueryBuilder\QueryBuilder
     */
    private $_qb;
    /**
     * @var \Mindy\Orm\Model
     */
    private $_model;
    /**
     * @var string
     */
    private $_tableAlias;

    abstract public function getData();

    /**
     * @return \Mindy\Event\EventManager
     */
    protected function getEventManager()
    {
        return Mindy::app()->getComponent('signal');
    }

    /**
     * @return \Mindy\QueryBuilder\Database\Mysql\Adapter|\Mindy\QueryBuilder\Database\Pgsql\Adapter|\Mindy\QueryBuilder\Database\Sqlite\Adapter
     * @throws \Mindy\Query\Exception\Exception
     */
    protected function getAdapter()
    {
        return $this->getDb()->getAdapter();
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * @param $db
     * @return $this
     */
    public function using($db)
    {
        if (($db instanceof Connection) === false) {
            // TODO refact, detach from Mindy::app()
            $db = Mindy::app()->db->getDb($db);
        }
        $this->_db = $db;
        return $this;
    }

    /**
     * @return \Mindy\Query\Connection
     */
    public function getDb()
    {
        /** @var \Mindy\Query\ConnectionManager $cm */
        if ($this->_db === null && Mindy::app()) {
            $this->_db = Mindy::app()->db->getDb();
        }
        return $this->_db;
    }

    /**
     * @param null $sql
     * @param array $params
     * @return \Mindy\Query\Command
     */
    public function createCommand($sql = null, $params = [])
    {
        return $this->getDb()->createCommand($sql, $params);
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return $this->_tableAlias;
    }

    protected function setTableAlias(QueryBuilder $qb, $tableName)
    {
        $this->_tableAlias = $qb->makeAliasKey($tableName);
        return $this;
    }

    /**
     * @return \Mindy\QueryBuilder\QueryBuilder
     */
    public function getQueryBuilder()
    {
        if ($this->_qb === null) {
            $builder = $this->getDb()->getQueryBuilder();
            $this->setTableAlias($builder, $this->getModel()->tableName());
            $builder->setAlias($this->getTableAlias());
            $model = $this->getModel();
            $meta = $model->getMeta();
            $builder->from($model->tableName());

            $fetchColumnCallback = new FetchColumnCallback($model, $meta);
            $callback = new LookupCallback($model);
            $joinCallback = new JoinCallback($model);
            $builder
                ->getLookupBuilder()
                ->setFetchColumnCallback($fetchColumnCallback)
                ->setCallback($callback)
                ->setJoinCallback($joinCallback);
            $this->_qb = $builder;
        }
        return $this->_qb;
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
        $className = get_class($this->getModel());
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