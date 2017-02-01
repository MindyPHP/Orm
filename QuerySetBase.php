<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm;

use ArrayAccess;
use Doctrine\DBAL\Connection;
use Exception;
use IteratorAggregate;
use Mindy\Orm\Callback\FetchColumnCallback;
use Mindy\Orm\Callback\JoinCallback;
use Mindy\Orm\Callback\LookupCallback;
use Mindy\QueryBuilder\QueryBuilder;
use Serializable;

/**
 * Class QuerySetBase.
 */
abstract class QuerySetBase implements IteratorAggregate, ArrayAccess, Serializable
{
    /**
     * @var string the name of the ActiveRecord class
     */
    public $modelClass;
    /**
     * @var bool whether to return each record as an array. If false (default), an object
     *           of [[modelClass]] will be created to represent each record.
     */
    protected $asArray;
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var DataIterator
     */
    private $_iterator;
    /**
     * @var \Mindy\QueryBuilder\QueryBuilder
     */
    protected $queryBuilder;
    /**
     * @var \Mindy\Orm\Model
     */
    private $model;
    /**
     * @var string
     */
    private $_tableAlias;

    /**
     * QuerySetBase constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function __clone()
    {
        if ($this->queryBuilder) {
            $this->queryBuilder = clone $this->queryBuilder;
        }
    }

    /**
     * @throws Exception
     *
     * @return \Mindy\QueryBuilder\BaseAdapter|\Mindy\QueryBuilder\Interfaces\ISQLGenerator
     */
    protected function getAdapter()
    {
        return QueryBuilder::getInstance($this->getConnection())->getAdapter();
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param ModelInterface $model
     *
     * @return $this
     */
    public function setModel(ModelInterface $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @param \Doctrine\Dbal\Connection $connection
     *
     * @return $this
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->model->getConnection();
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
        if ($this->queryBuilder === null) {
            $builder = QueryBuilder::getInstance($this->getConnection());
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
            $this->queryBuilder = $builder;
        }

        return $this->queryBuilder;
    }

    /**
     * Sets the [[asArray]] property.
     *
     * @param bool $value whether to return the query results in terms of arrays instead of Active Records
     *
     * @return static the query object itself
     */
    public function asArray($value = true)
    {
        $this->asArray = $value;

        return $this;
    }

    /**
     * @param $row array
     *
     * @return ModelInterface
     */
    public function createModel(array $row)
    {
        return $this->getModel()->create($row);
    }

    /**
     * Converts found rows into model instances.
     *
     * @param array $rows
     *
     * @return array|ModelInterface[]
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
     * @return array
     */
    abstract public function all();

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator.
     *
     * @see http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     *                      <b>\Traversable</b>
     */
    public function getIterator()
    {
        if (!$this->_iterator) {
            $this->_iterator = new DataIterator($this->all(), [
                'asArray' => $this->asArray,
                'qs' => $this,
            ]);
        }

        return $this->_iterator;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     *              </p>
     *              <p>
     *              The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->getIterator()->offsetExists($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed can return all value types
     */
    public function offsetGet($offset)
    {
        return $this->getIterator()->offsetGet($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     */
    public function offsetSet($offset, $value)
    {
        $this->getIterator()->offsetSet($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     */
    public function offsetUnset($offset)
    {
        $this->getIterator()->offsetUnset($offset);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object.
     *
     * @see http://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(get_object_vars($this));
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object.
     *
     * @see http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     *
     * @return Model[]
     */
    public function unserialize($data)
    {
        $props = unserialize($data);
        foreach ($props as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
