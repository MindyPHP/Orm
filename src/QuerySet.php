<?php

namespace Mindy\Orm;

use Mindy\Exception\Exception;
use Mindy\Creator\Creator;
use Mindy\Orm\Exception\MultipleObjectsReturned;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\QueryBuilder\Aggregation\Aggregation;
use Mindy\QueryBuilder\Aggregation\Avg;
use Mindy\QueryBuilder\Aggregation\Count;
use Mindy\QueryBuilder\Aggregation\Max;
use Mindy\QueryBuilder\Aggregation\Min;
use Mindy\QueryBuilder\Aggregation\Sum;
use Mindy\QueryBuilder\Q\QAndNot;
use Mindy\QueryBuilder\Q\QOrNot;
use Mindy\QueryBuilder\QueryBuilder;

/**
 * Class QuerySet
 * @package Mindy\Orm
 */
class QuerySet extends QuerySetBase
{
    /**
     * @var array a list of relations that this query should be performed with
     */
    protected $with = [];

    /**
     * Executes query and returns all results as an array.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all()
    {
        $rows = $this->getConnection()->query($this->allSql())->fetchAll();
        if ($this->asArray) {
            return !empty($this->with) ? $this->populateWith($rows) : $rows;
        }

        return $this->createModels($rows);
    }

    /**
     * @param int $batchSize
     * @return \Mindy\Orm\BatchDataIterator
     */
    public function batch($batchSize = 100)
    {
        return new BatchDataIterator($this->getConnection(), [
            'qs' => $this,
            'batchSize' => $batchSize,
            'each' => false,
            'asArray' => $this->asArray,
        ]);
    }

    /**
     * @param int $batchSize
     * @return \Mindy\Orm\BatchDataIterator
     */
    public function each($batchSize = 100)
    {
        return new BatchDataIterator($this->getConnection(), [
            'qs' => $this,
            'batchSize' => $batchSize,
            'each' => true,
            'asArray' => $this->asArray,
        ]);
    }

    /**
     * @param array $columns
     * @param bool $flat
     * @return array
     */
    public function valuesList($columns, $flat = false)
    {
        $qb = clone $this->getQueryBuilder();
        $rows = $this->getConnection()->query($qb->select($columns)->toSQL())->fetchAll();

        if ($flat) {
            $flatArr = [];
            foreach ($rows as $item) {
                $flatArr = array_merge($flatArr, array_values($item));
            }
            return $flatArr;
        } else {
            return $rows;
        }
    }

    /**
     * Update records
     * @param array $attributes
     * @return int updated records
     */
    public function update(array $attributes)
    {
        return $this->getConnection()->executeUpdate($this->updateSql($attributes));
    }

    /**
     * @param array $attributes
     * @return string
     */
    public function updateSql(array $attributes)
    {
        $attrs = [];
        foreach ($attributes as $key => $value) {
            $attrs[$this->getModel()->convertToPrimaryKeyName($key)] = $value;
        }
        return $this->getQueryBuilder()->setTypeUpdate()->update($this->model->tableName(), $attrs)->toSQL();
    }

    /**
     * @param array $attributes
     * @return array
     */
    public function getOrCreate(array $attributes) : array
    {
        $model = $this->get($attributes);
        if ($model === null) {
            $className = get_class($this->getModel());
            /** @var Model $model */
            $model = new $className($attributes);
            $model->save();
            return [$model, true];
        }

        return [$model, false];
    }

    /**
     * @param array $attributes
     * @param array $updateAttributes
     * @return ModelInterface|Orm|null
     */
    public function updateOrCreate(array $attributes, array $updateAttributes)
    {
        $model = $this->get($attributes);
        if ($model === null) {
            $model = $this->getModel()->create();
        }
        $model->setAttributes($updateAttributes);
        $model->save();
        return $model;
    }

    /**
     * Paginate models
     * @param int $page
     * @param int $pageSize
     * @return $this
     */
    public function paginate($page = 1, $pageSize = 10)
    {
        $this->getQueryBuilder()->paginate($page, $pageSize);
        return $this;
    }

    /**
     * @return string
     */
    public function allSql()
    {
        $qb = clone $this->getQueryBuilder();
        return $qb->setTypeSelect()->toSQL();
    }

    /**
     * @param array $filter
     * @return string
     */
    public function getSql($filter = [])
    {
        if ($filter) {
            $this->filter($filter);
        }
        $qb = clone $this->getQueryBuilder();
        return $qb->setTypeSelect()->toSQL();
    }

    /**
     * Executes query and returns a single row of result.
     * @param array $filter
     * @return ModelInterface|array|null
     * @throws MultipleObjectsReturned
     */
    public function get($filter = [])
    {
        $rows = $this->getConnection()->query($this->getSql($filter))->fetchAll();
        if (count($rows) > 1) {
            throw new MultipleObjectsReturned();
        } elseif (count($rows) === 0) {
            return null;
        }

        if (!empty($this->with)) {
            $rows = $this->populateWith($rows);
        }
        $row = array_shift($rows);
        if ($this->asArray) {
            return $row;
        } else {
            $model = $this->createModel($row);
            $model->setIsNewRecord(false);
            return $model;
        }
    }

    /**
     * Converts array prefix to string key
     * @param array $prefix
     * @return string
     */
    protected function prefixToKey(array $prefix)
    {
        return implode('__', $prefix);
    }

    /**
     * Searching closest already connected relation
     * Example: User::objects()->filter(['group__name' => 'Admin', 'group__list__pk' => 2])
     * at the second time we already have connected 'group' relation, return it
     * @param $prefix
     * @return array
     */
    protected function searchChain($prefix)
    {
        $model = $this->model;
        $alias = $this->tableAlias;

        $prefixRemains = [];
        $chainRemains = [];

        foreach ($prefix as $relationName) {
            $chain[] = $relationName;
            if ($founded = $this->getChain($chain)) {
                $model = $founded['model'];
                $alias = $founded['alias'];
                $prefixRemains = [];
                $chainRemains = $chain;
            } else {
                $prefixRemains[] = $relationName;
            }
        }

        return [$model, $alias, $prefixRemains, $chainRemains];
    }

    public function with(array $with)
    {
        foreach ($with as $name => $fields) {
            if (is_numeric($name)) {
                $name = $fields;
            }

            if ($this->model->getMeta()->hasRelatedField($name)) {
                $this->with[] = $name;
                $this->getOrCreateChainAlias([$name], true, true, is_array($fields) ? $fields : []);
            }
        }
        return $this;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function filter($query)
    {
        if (is_array($query)) {
            $newQuery = array_map(function ($value) {
                if ($value instanceof Model) {
                    return $value->pk;
                } else if ($value instanceof Manager || $value instanceof QuerySet) {
                    return $value->getQueryBuilder();
                }
                return $value;
            }, $query);
        } else {
            $newQuery = $query;
        }
        $this->getQueryBuilder()->where($newQuery);
        return $this;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function orFilter(array $query)
    {
        $this->getQueryBuilder()->orWhere($query);
        return $this;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function exclude(array $query)
    {
        $this->getQueryBuilder()->where(new QAndNot($query));
        return $this;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function orExclude(array $query)
    {
        $this->getQueryBuilder()->orWhere(new QOrNot($query));
        return $this;
    }

    /**
     * Converts name => `name`, user.name => `user`.`name`
     * @param string $name Column name
     * @return string Quoted column name
     */
    public function quoteColumnName($name)
    {
        return $this->getConnection()->quoteColumnName($name);
    }

    /**
     * Order by alias
     * @param $columns
     * @return $this
     */
    public function order($columns)
    {
        if (is_array($columns)) {
            $newColumns = array_map(function ($value) {
                if ($value instanceof Model) {
                    return $value->pk;
                } else if ($value instanceof Manager || $value instanceof QuerySet) {
                    return $value->getQueryBuilder();
                } else if (is_string($value)) {
                    $direction = substr($value, 0, 1) === '-' ? '-' : '';
                    $column = substr($value, 1);
                    if ($this->getModel()->getMeta()->hasForeignField($column)) {
                        return $direction . $column . '_id';
                    } else {
                        return $value;
                    }
                }
                return $value;
            }, $columns);
        } else {
            $newColumns = $columns;
        }
        $this->getQueryBuilder()->order($newColumns);
        return $this;
    }

    /**
     * @param null|string|array $q
     * @return float|int
     */
    public function sum($q)
    {
        return $this->aggregate(new Sum($q));
    }

    /**
     * @param string $q
     * @return float|int
     */
    public function sumSql($q)
    {
        return $this->buildAggregateSql(new Sum($q));
    }

    /**
     * @param null|string|array $q
     * @return float|int
     */
    public function average($q)
    {
        return $this->aggregate(new Avg($q));
    }

    /**
     * @param null|string|array $q
     * @return float|int
     */
    public function averageSql($q)
    {
        return $this->buildAggregateSql(new Avg($q));
    }

    /**
     * @param $columns
     * @param null $option
     * @return $this
     */
    public function select($columns, $option = null)
    {
        $this->getQueryBuilder()->select($columns, $option);
        return $this;
    }

    private function buildAggregateSql(Aggregation $q)
    {
        $qb = clone $this->getQueryBuilder();
        
        list($order, $orderOptions) = $qb->getOrder();
        $select = $qb->getSelect();
        $sql = $qb->order(null)->select($q)->toSQL();
        $qb->select($select)->order($order, $orderOptions);
        return $sql;
    }

    private function aggregate(Aggregation $q)
    {
        $result = $this->getConnection()->query($this->buildAggregateSql($q))->fetch();
        $value = end($result);
        return strpos($value, '.') !== false ? floatval($value) : intval($value);
    }

    /**
     * @param null|string|array $q
     * @return float|int
     */
    public function min($q)
    {
        return $this->aggregate(new Min($q));
    }

    /**
     * @param null|string|array $q
     * @return float|int
     */
    public function minSql($q)
    {
        return $this->buildAggregateSql(new Min($q));
    }

    /**
     * @param null|string|array $q
     * @return float|int
     */
    public function max($q)
    {
        return $this->aggregate(new Max($q));
    }

    /**
     * @param null|string|array $q
     * @return float|int
     */
    public function maxSql($q)
    {
        return $this->buildAggregateSql(new Max($q));
    }

    public function delete()
    {
        $statement = $this->getConnection()->query($this->deleteSql());
        return $statement->execute();
    }

    public function deleteSql()
    {
//        if ($this->filterHasJoin()) {
//            $this->prepareConditions();
//            return $this->createCommand()->delete($tableName, [
//                $this->getPrimaryKeyName() => $this->valuesList(['pk'], true)
//            ], $this->params);
//        }

        $builder = $this->getQueryBuilder()
            ->setTypeDelete()
            ->setAlias(null);
        return $builder->toSQL();
    }

    /**
     * @param null|array|string $q
     * @return string
     */
    public function countSql($q = '*')
    {
        return $this->buildAggregateSql(new Count($q));
    }

    /**
     * @param string $q
     * @return int
     */
    public function count($q = '*')
    {
        return $this->aggregate(new Count($q));
    }

    /**
     * Convert array like:
     * >>> ['developer__id' => '1', 'developer__name' = 'Valve']
     * to:
     * >>> ['developer' => ['id' => '1', 'name' => 'Valve']]
     *
     * @param $data
     * @return array
     */
    private function populateWith($data)
    {
        $newData = [];
        foreach ($data as $row) {
            $tmp = [];
            foreach ($row as $key => $value) {
                if (strpos($key, '__') !== false) {
                    list($prefix, $postfix) = explode('__', $key);
                    if (!isset($tmp[$prefix])) {
                        $tmp[$prefix] = [];
                    }
                    $tmp[$prefix][$postfix] = $value;
                } else {
                    $tmp[$key] = $value;
                }
            }
            $newData[] = $tmp;
        }
        return $newData;
    }

    /**
     * Truncate table
     * @return int
     */
    public function truncate()
    {
        $connection = $this->getConnection();
        $adapter = QueryBuilder::getInstance($connection)->getAdapter();
        $tableName = $adapter->quoteTableName($adapter->getRawTableName($this->model->tableName()));
        $q = $connection->getDatabasePlatform()->getTruncateTableSQL($tableName);
        return $connection->executeUpdate($q);
    }

    /**
     * @param mixed $fields
     * @return $this
     */
    public function distinct($fields = true)
    {
        $this->getQueryBuilder()->distinct($fields);
        return $this;
    }

    /**
     * @param $columns
     * @return $this
     */
    public function group($columns)
    {
        $this->getQueryBuilder()->group($columns);
        return $this;
    }

    public function limit($limit)
    {
        $this->getQueryBuilder()->limit($limit);
        return $this;
    }

    public function offset($offset)
    {
        $this->getQueryBuilder()->offset($offset);
        return $this;
    }
}
