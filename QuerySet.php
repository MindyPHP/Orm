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
use Mindy\QueryBuilder\QueryBuilderFactory;
use Mindy\QueryBuilder\Utils\TableNameResolver;

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
     * Executes query and returns all results as an array.
     * If null, the DB connection returned by [[modelClass]] will be used.
     *
     * @param string $sql
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function raw(string $sql)
    {
        $rows = $this
            ->getConnection()
            ->query($sql)
            ->fetchAll();

        if ($this->asArray) {
            return $rows;
        }

        return $this->createModels($rows);
    }

    /**
     * Executes query and returns all results as an array.
     * If null, the DB connection returned by [[modelClass]] will be used.
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all(): array
    {
        $rows = $this
            ->getConnection()
            ->query($this->allSql())
            ->fetchAll();

        if ($this->asArray) {
            if (false === empty($this->with)) {
                return $this->populateWith($rows);
            }

            return $rows;
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
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return array
     */
    public function valuesList($columns, $flat = false): array
    {
        $qb = clone $this->getQueryBuilder();
        $rows = $this
            ->getConnection()
            ->query($qb->select($columns)->toSQL())
            ->fetchAll();

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
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
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
            ->update()
            ->table($this->getModel()->tableName())
            ->values($attrs)
            ->toSQL();

        return $sql;
    }

    /**
     * @param array $attributes
     *
     * @throws MultipleObjectsReturned
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
     * @throws MultipleObjectsReturned
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
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return $this
     */
    public function paginate($page = 1, $pageSize = 10)
    {
        $this
            ->getQueryBuilder()
            ->paginate($page, $pageSize);

        return $this;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return string
     */
    public function allSql(): string
    {
        $qb = clone $this->getQueryBuilder();

        return $qb->toSQL();
    }

    /**
     * @param array $filter
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return string
     */
    public function getSql($filter = []): string
    {
        if ($filter) {
            $this->filter($filter);
        }
        $qb = clone $this->getQueryBuilder();

        return $qb->toSQL();
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
            return null;
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
        $this
            ->getQueryBuilder()
            ->where($this->convertQuery($query));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orFilter($query)
    {
        $this
            ->getQueryBuilder()
            ->orWhere($this->convertQuery($query));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function exclude($query)
    {
        $this
            ->getQueryBuilder()
            ->where(new QAndNot($this->convertQuery($query)));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orExclude($query)
    {
        $this
            ->getQueryBuilder()
            ->orWhere(new QOrNot($this->convertQuery($query)));

        return $this;
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
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return string
     */
    public function sumSql($q): string
    {
        return $this->buildAggregateSql(new Sum($q));
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
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return string
     */
    public function averageSql($q): string
    {
        return $this->buildAggregateSql(new Avg($q));
    }

    /**
     * @param $columns
     * @param null $option
     *
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return $this
     */
    public function select($columns, $option = null)
    {
        $this
            ->getQueryBuilder()
            ->select($columns, $option);

        return $this;
    }

    /**
     * @param Aggregation $q
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return string
     */
    private function buildAggregateSql(Aggregation $q): string
    {
        $qb = clone $this->getQueryBuilder();

        return $qb
            ->resetQueryPart('orderBy')
            ->select($q)
            ->toSQL();
    }

    /**
     * @param Aggregation $q
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
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

        return false !== strpos((string) $value, '.') ? floatval($value) : intval($value);
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
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return string
     */
    public function minSql($q): string
    {
        return $this->buildAggregateSql(new Min($q));
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
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return string
     */
    public function maxSql($q): string
    {
        return $this->buildAggregateSql(new Max($q));
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        return $this
            ->getConnection()
            ->query($this->deleteSql())
            ->execute();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return string
     */
    public function deleteSql(): string
    {
//        if ($this->filterHasJoin()) {
//            $this->prepareConditions();
//            return $this->createCommand()->delete($tableName, [
//                $this->getPrimaryKeyName() => $this->valuesList(['pk'], true)
//            ], $this->params);
//        }

        return $this
            ->getQueryBuilder()
            ->delete()
            ->table($this->getModel()->tableName())
            ->setAlias(null)
            ->toSQL();
    }

    /**
     * @param null|array|string $q
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return string
     */
    public function countSql($q = '*'): string
    {
        return $this->buildAggregateSql(new Count($q));
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
    private function populateWith(array $data): array
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
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return int
     */
    public function truncate()
    {
        $sql = $this->truncateSql();

        return $this
            ->getConnection()
            ->executeUpdate($sql);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Mindy\QueryBuilder\Exception\NotSupportedException
     *
     * @return string
     */
    public function truncateSql(): string
    {
        $connection = $this->getConnection();
        $builder = QueryBuilderFactory::getQueryBuilder($connection);
        $tableName = $builder->getQuotedName(TableNameResolver::getTableName($this->getModel()->tableName()));

        return $connection
            ->getDatabasePlatform()
            ->getTruncateTableSQL($tableName);
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
    public function limit($limit)
    {
        $this
            ->getQueryBuilder()
            ->limit($limit);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offset($offset)
    {
        $this
            ->getQueryBuilder()
            ->offset($offset);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function having($having)
    {
        $this
            ->getQueryBuilder()
            ->having($having);

        return $this;
    }
}
