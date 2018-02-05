<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

use Mindy\Orm\Exception\MultipleObjectsReturned;
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
 * Class QuerySet.
 */
class QuerySet extends QuerySetBase
{
    /**
     * @var array a list of relations that this query should be performed with
     */
    protected $with = [];
    /**
     * @var string
     */
    protected $sql;

    /**
     * Executes query and returns all results as an array.
     * If null, the DB connection returned by [[modelClass]] will be used.
     *
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all()
    {
        $sql = null === $this->sql ? $this->allSql() : $this->sql;
        $rows = $this->getConnection()->query($sql)->fetchAll();
        if ($this->asArray) {
            return !empty($this->with) ? $this->populateWith($rows) : $rows;
        }

        return $this->createModels($rows);
    }

    /**
     * @param int $batchSize
     *
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
     *
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
     * @param bool  $flat
     *
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
        }

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $attributes)
    {
        return $this->getConnection()->executeUpdate($this->updateSql($attributes));
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    public function updateSql(array $attributes)
    {
        $attrs = [];
        foreach ($attributes as $key => $value) {
            $attrs[$this->getModel()->convertToPrimaryKeyName($key)] = $value;
        }

        $sql = $this
            ->getQueryBuilder()
            ->update($this->getModel()->tableName())
            ->values($attrs)
            ->toSQL();

        return $sql;
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    public function getOrCreate(array $attributes)
    {
        $model = $this->get($attributes);
        if (null === $model) {
            $className = get_class($this->getModel());
            /** @var Model $model */
            $model = new $className($attributes);
            $model->save();

            return [$model, true];
        }

        return [$model, false];
    }

    /**
     * Find and update model if exists. Else create model.
     *
     * @param array $attributes
     * @param array $updateAttributes
     *
     * @return ModelInterface|bool
     */
    public function updateOrCreate(array $attributes, array $updateAttributes)
    {
        $model = $this->get($attributes);
        if (null === $model) {
            $model = $this->getModel()->create();
            $model->setIsNewRecord(true);
        }

        $model->setAttributes($updateAttributes);
        if ($model->save()) {
            return $model;
        }

        return false;
    }

    /**
     * Paginate models.
     *
     * @param int $page
     * @param int $pageSize
     *
     * @return $this
     */
    public function paginate($page = 1, $pageSize = 10)
    {
        $this->getQueryBuilder()->paginate($page, $pageSize);

        return $this;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return string
     */
    public function allSql()
    {
        $qb = clone $this->getQueryBuilder();

        $sql = $qb->toSQL();

        return $sql;
    }

    /**
     * @param array $filter
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return string
     */
    public function getSql($filter = [])
    {
        if ($filter) {
            $this->filter($filter);
        }
        $qb = clone $this->getQueryBuilder();

        $sql = $qb->toSQL();

        return $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function get($filter = [])
    {
        $rows = $this->getConnection()->query($this->getSql($filter))->fetchAll();
        if (count($rows) > 1) {
            throw new MultipleObjectsReturned();
        } elseif (0 === count($rows)) {
            return;
        }

        if (!empty($this->with)) {
            $rows = $this->populateWith($rows);
        }
        $row = array_shift($rows);
        if ($this->asArray) {
            return $row;
        }
        $model = $this->createModel($row);
        $model->setIsNewRecord(false);

        return $model;
    }

    /**
     * @param string $sql
     *
     * @return $this
     */
    public function setSql($sql)
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * Converts array prefix to string key.
     *
     * @param array $prefix
     *
     * @return string
     */
    protected function prefixToKey(array $prefix)
    {
        return implode('__', $prefix);
    }

    /**
     * todo remove me
     * Searching closest already connected relation
     * Example: User::objects()->filter(['group__name' => 'Admin', 'group__list__pk' => 2])
     * at the second time we already have connected 'group' relation, return it.
     *
     * @param $prefix
     *
     * @return array
     */
    protected function searchChain($prefix)
    {
        $model = $this->getModel();
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

    /**
     * todo remove me
     *
     * @param $with
     *
     * @return $this
     */
    public function with($with)
    {
        if (!is_array($with)) {
            $with = [$with];
        }

        foreach ($with as $name => $fields) {
            if (is_numeric($name)) {
                $name = $fields;
            }

            if ($this->getModel()->getMeta()->hasRelatedField($name)) {
                $this->with[] = $name;
                $this->getOrCreateChainAlias([$name], true, true, is_array($fields) ? $fields : []);
            }
        }

        return $this;
    }

    /**
     * @param $query
     *
     * @return array
     */
    protected function convertQuery($query)
    {
        if (is_array($query)) {
            return array_map(function ($value) {
                if ($value instanceof Model) {
                    return $value->pk;
                } elseif ($value instanceof Manager || $value instanceof QuerySet) {
                    return $value->getQueryBuilder();
                }

                return $value;
            }, $query);
        } else {
            return $query;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function filter($query)
    {
        $this->getQueryBuilder()->where($this->convertQuery($query));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orFilter($query)
    {
        $this->getQueryBuilder()->orWhere($this->convertQuery($query));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function exclude($query)
    {
        $this->getQueryBuilder()->where(new QAndNot($this->convertQuery($query)));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orExclude($query)
    {
        $this->getQueryBuilder()->orWhere(new QOrNot($this->convertQuery($query)));

        return $this;
    }

    /**
     * Converts name => `name`, user.name => `user`.`name`.
     *
     * @param string $name Column name
     *
     * @return string Quoted column name
     */
    public function quoteColumnName($name)
    {
        return $this->getAdapter()->quoteColumn($name);
    }

    /**
     * {@inheritdoc}
     */
    public function order($columns)
    {
        if (is_array($columns)) {
            $newColumns = array_map(function ($value) {
                if ($value instanceof Model) {
                    return $value->pk;
                } elseif ($value instanceof Manager || $value instanceof QuerySet) {
                    return $value->getQueryBuilder();
                } elseif (is_string($value)) {
                    $direction = '-' === substr($value, 0, 1) ? '-' : '';
                    $column = substr($value, 1);
                    if ($this->getModel()->getMeta()->hasForeignField($column)) {
                        return $direction.$column.'_id';
                    }

                    return $value;
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
     * {@inheritdoc}
     */
    public function sum($q)
    {
        return $this->aggregate(new Sum($q));
    }

    /**
     * @param string $q
     *
     * @return float|int
     */
    public function sumSql($q)
    {
        $sql =  $this->buildAggregateSql(new Sum($q));

        return $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function average($q)
    {
        return $this->aggregate(new Avg($q));
    }

    /**
     * @param null|string|array $q
     *
     * @return float|int
     */
    public function averageSql($q)
    {
        $sql = $this->buildAggregateSql(new Avg($q));

        return $sql;
    }

    /**
     * @param $columns
     * @param null $option
     *
     * @return $this
     */
    public function select($columns, $option = null)
    {
        $this->getQueryBuilder()->select($columns, $option);

        return $this;
    }

    /**
     * @param Aggregation $q
     *
     * @return string
     */
    private function buildAggregateSql(Aggregation $q)
    {
        $qb = clone $this->getQueryBuilder();

        $sql = (clone $qb)
            ->resetQueryPart('orderBy')
            ->select($q)
            ->toSQL();

        return $sql;
    }

    /**
     * @param Aggregation $q
     *
     * @return float|int
     */
    private function aggregate(Aggregation $q)
    {
        $sql = $this->buildAggregateSql($q);
        $statement = $this->getConnection()->query($sql);
        $value = $statement->fetch();
        if (is_array($value)) {
            $value = end($value);
        }

        return false !== strpos((string)$value, '.') ? floatval($value) : intval($value);
    }

    /**
     * {@inheritdoc}
     */
    public function min($q)
    {
        return $this->aggregate(new Min($q));
    }

    /**
     * @param null|string|array $q
     *
     * @return float|int
     */
    public function minSql($q)
    {
        $sql =  $this->buildAggregateSql(new Min($q));

        return $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function max($q)
    {
        return $this->aggregate(new Max($q));
    }

    /**
     * @param null|string|array $q
     *
     * @return float|int
     */
    public function maxSql($q)
    {
        $sql = $this->buildAggregateSql(new Max($q));

        return $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $statement = $this->getConnection()->query($this->deleteSql());

        return $statement->execute();
    }

    /**
     * @return string
     */
    public function deleteSql()
    {
//        if ($this->filterHasJoin()) {
//            $this->prepareConditions();
//            return $this->createCommand()->delete($tableName, [
//                $this->getPrimaryKeyName() => $this->valuesList(['pk'], true)
//            ], $this->params);
//        }

        $sql = $this
            ->getQueryBuilder()
            ->delete($this->getModel()->tableName())
            ->setAlias(null)
            ->toSQL();

        return $sql;
    }

    /**
     * @param null|array|string $q
     *
     * @return string
     */
    public function countSql($q = '*')
    {
        $sql = $this->buildAggregateSql(new Count($q));

        return $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function count($q = '*')
    {
        return $this->aggregate(new Count($q));
    }

    /**
     * Convert array like:
     * >>> ['developer__id' => '1', 'developer__name' = 'Valve']
     * to:
     * >>> ['developer' => ['id' => '1', 'name' => 'Valve']].
     *
     * @param $data
     *
     * @return array
     */
    private function populateWith($data)
    {
        $newData = [];
        foreach ($data as $row) {
            $tmp = [];
            foreach ($row as $key => $value) {
                if (false !== strpos($key, '__')) {
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
     * Truncate table.
     *
     * @return int
     */
    public function truncate()
    {
        $connection = $this->getConnection();
        $adapter = QueryBuilder::getInstance($connection)->getAdapter();
        $tableName = $adapter->quoteTableName($adapter->getRawTableName($this->getModel()->tableName()));
        $q = $connection->getDatabasePlatform()->getTruncateTableSQL($tableName);

        return $connection->executeUpdate($q);
    }

    /**
     * {@inheritdoc}
     */
    public function distinct($fields = true)
    {
        $this->getQueryBuilder()->distinct($fields);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function group($columns)
    {
        $this->getQueryBuilder()->group($columns);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addGroupBy($columns)
    {
        $this->getQueryBuilder()->addGroupBy($columns);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function limit($limit)
    {
        $this->getQueryBuilder()->limit($limit);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offset($offset)
    {
        $this->getQueryBuilder()->offset($offset);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function having($having)
    {
        $this->getQueryBuilder()->having($having);

        return $this;
    }
}
