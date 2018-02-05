<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

use Exception;
use Mindy\Orm\Callback\FetchColumnCallback;
use Mindy\Orm\Callback\JoinCallback;
use Mindy\Orm\Callback\LookupCallback;
use Mindy\QueryBuilder\AdapterInterface;
use Mindy\QueryBuilder\QueryBuilder;
use Mindy\QueryBuilder\QueryBuilderFactory;
use Serializable;

/**
 * Class QuerySetBase.
 */
abstract class QuerySetBase implements QuerySetInterface, Serializable
{
    use ConnectionAwareTrait;

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
     * @return \Mindy\QueryBuilder\AdapterInterface
     */
    protected function getAdapter(): AdapterInterface
    {
        return $this->getQueryBuilder()->getAdapter();
    }

    /**
     * @return Model
     */
    public function getModel(): ModelInterface
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
     * @return string
     */
    public function getTableAlias()
    {
        return $this->_tableAlias;
    }

    /**
     * @param QueryBuilder $qb
     * @param $tableName
     *
     * @return $this
     */
    protected function setTableAlias(QueryBuilder $qb, $tableName)
    {
        $this->_tableAlias = $qb->makeAliasKey($tableName);

        return $this;
    }

    /**
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return \Mindy\QueryBuilder\QueryBuilder
     */
    public function getQueryBuilder()
    {
        if (null === $this->queryBuilder) {
            $builder = QueryBuilderFactory::getQueryBuilder($this->getConnection());
            $this->setTableAlias($builder, $this->getModel()->tableName());
            $builder->setAlias($this->getTableAlias());
            $model = $this->getModel();
            $meta = $model->getMeta();
            $builder->table($model->tableName());

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
     * @param array $row
     *
     * @return ModelInterface
     */
    public function createModel(array $row): ModelInterface
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
    abstract public function all(): array;

    /**
     * {@inheritdoc}
     */
    public function getIterator(): DataIterator
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
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->getIterator()->offsetExists($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getIterator()->offsetGet($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->getIterator()->offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->getIterator()->offsetUnset($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(get_object_vars($this));
    }

    /**
     * {@inheritdoc}
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
