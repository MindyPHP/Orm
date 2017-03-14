<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

use Doctrine\DBAL\Connection;
use Iterator;

/**
 * BatchQueryResult represents a batch query from which you can retrieve data in batches.
 *
 * You usually do not instantiate BatchQueryResult directly. Instead, you obtain it by
 * calling [[Query::batch()]] or [[Query::each()]]. Because BatchQueryResult implements the `Iterator` interface,
 * you can iterate it to obtain a batch of data in each iteration. For example,
 *
 * ```php
 * $query = (new Query)->from('user');
 * foreach ($query->batch() as $i => $users) {
 *     // $users represents the rows in the $i-th batch
 * }
 * foreach ($query->each() as $user) {
 * }
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 */
class BatchDataIterator implements Iterator
{
    /**
     * @var bool
     */
    public $asArray;
    /**
     * @var \Mindy\Orm\QuerySet the query object associated with this batch query.
     *                          Do not modify this property directly unless after [[reset()]] is called explicitly.
     */
    public $qs;
    /**
     * @var \Doctrine\Dbal\Connection the DB connection to be used when performing batch query.
     *                                If null, the "db" application component will be used.
     */
    protected $connection;
    /**
     * @var int the number of rows to be returned in each batch
     */
    public $batchSize = 100;
    /**
     * @var bool whether to return a single row during each iteration.
     *           If false, a whole batch of rows will be returned in each iteration.
     */
    public $each = false;
    /**
     * @var DataReader the data reader associated with this batch query
     */
    private $_dataReader;
    /**
     * @var array the data retrieved in the current batch
     */
    private $_batch;
    /**
     * @var mixed the value for the current iteration
     */
    private $_value;
    /**
     * @var string|int the key for the current iteration
     */
    private $_key;

    /**
     * BatchDataIterator constructor.
     *
     * @param array $config
     */
    public function __construct(Connection $connection, array $config = [])
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
        $this->connection = $connection;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        // make sure cursor is closed
        $this->reset();
    }

    /**
     * Resets the batch query.
     * This method will clean up the existing batch query so that a new batch query can be performed.
     */
    public function reset()
    {
        if ($this->_dataReader !== null) {
            $this->_dataReader->close();
        }
        $this->_dataReader = null;
        $this->_batch = null;
        $this->_value = null;
        $this->_key = null;
    }

    /**
     * Resets the iterator to the initial state.
     * This method is required by the interface Iterator.
     */
    public function rewind()
    {
        $this->reset();
        $this->next();
    }

    /**
     * Moves the internal pointer to the next dataset.
     * This method is required by the interface Iterator.
     */
    public function next()
    {
        if ($this->_batch === null || !$this->each || $this->each && next($this->_batch) === false) {
            $this->_batch = $this->fetchData();
            reset($this->_batch);
        }
        if ($this->each) {
            $this->_value = current($this->_batch);
            if (key($this->_batch) !== null) {
                ++$this->_key;
            } else {
                $this->_key = null;
            }
        } else {
            $this->_value = $this->_batch;
            $this->_key = $this->_key === null ? 0 : $this->_key + 1;
        }
    }

    /**
     * Fetches the next batch of data.
     *
     * @return array the data fetched
     */
    protected function fetchData()
    {
        if ($this->_dataReader === null) {
            $qb = clone $this->qs->getQueryBuilder();
            $this->_dataReader = new DataReader($this->connection->query($qb->toSQL()));
        }
        $rows = [];
        $count = 0;
        while ($count++ < $this->batchSize && ($row = $this->_dataReader->read())) {
            $rows[] = $row;
        }

        return $this->asArray ? $rows : $this->qs->createModels($rows);
    }

    /**
     * Returns the index of the current dataset.
     * This method is required by the interface Iterator.
     *
     * @return int the index of the current row
     */
    public function key()
    {
        return $this->_key;
    }

    /**
     * Returns the current dataset.
     * This method is required by the interface Iterator.
     *
     * @return mixed the current dataset
     */
    public function current()
    {
        return $this->_value;
    }

    /**
     * Returns whether there is a valid dataset at the current position.
     * This method is required by the interface Iterator.
     *
     * @return bool whether there is a valid dataset at the current position
     */
    public function valid()
    {
        return !empty($this->_batch);
    }
}
