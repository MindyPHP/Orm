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
 * @date 18/07/14.07.2014 16:54
 */

namespace Mindy\Orm;


use ArrayAccess;
use Countable;
use Iterator;
use Mindy\Core\Object;

/**
 * TODO unused prototype for next refactoring
 * Class DataIterator
 * @package Mindy\Orm
 */
class DataIterator extends Object implements Iterator, ArrayAccess, Countable
{
    /**
     * @var QuerySet
     */
    public $qs;
    /**
     * @var bool
     */
    public $asArray = false;
    /**
     * @var string|Model
     */
    public $modelClass;
    /**
     * @var Model[]
     */
    protected $_models = [];
    /**
     * @var null
     */
    protected $_data = [];

    /**
     * @param QuerySet $qs
     * @param array $config
     */
    public function __construct(QuerySet $qs, $config = [])
    {
        $this->qs = $qs;
        parent::__construct($config);
    }

    protected function createModel($row)
    {
        $class = $this->modelClass;
        return $class::create($row);
    }

    /**
     * Converts found rows into model instances
     * @param array $rows
     * @return array|Orm[]
     */
    protected function createModels($rows)
    {
        $models = [];
        foreach ($rows as $row) {
            $models[] = $this->createModel($row);
        }
        return $models;
    }

    /**
     * @return array|Model[]
     */
    public function getData()
    {
        if ($this->_data === null) {
            $this->_data = $this->qs->all();
        }
        return $this->_data;
    }

    /**
     * @return mixed|void
     */
    public function rewind()
    {
        return reset($this->_data);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        $item = current($this->_data);
        return $this->asArray ? $item : $this->createModel($item);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return key($this->_data);
    }

    /**
     * @return mixed|void
     */
    public function next()
    {
        return next($this->_data);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return key($this->_data) !== null;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->asArray ? $this->data[$offset] : $this->createModel($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }
}
