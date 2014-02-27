<?php
/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Mindy\Orm;

use InvalidArgumentException;
use Mindy\Exception\Exception;
use Mindy\Query\Query;
use Mindy\Exception\InvalidCallException;

/**
 * ActiveQuery represents a DB query associated with an Active Record class.
 *
 * ActiveQuery instances are usually created by [[ActiveRecord::find()]] and [[ActiveRecord::findBySql()]].
 *
 * ActiveQuery mainly provides the following methods to retrieve the query results:
 *
 * - [[one()]]: returns a single record populated with the first row of data.
 * - [[all()]]: returns all records based on the query results.
 * - [[count()]]: returns the number of records.
 * - [[sum()]]: returns the sum over the specified column.
 * - [[average()]]: returns the average over the specified column.
 * - [[min()]]: returns the min over the specified column.
 * - [[max()]]: returns the max over the specified column.
 * - [[scalar()]]: returns the value of the first column in the first row of the query result.
 * - [[column()]]: returns the value of the first column in the query result.
 * - [[exists()]]: returns a value indicating whether the query result has data or not.
 *
 * Because ActiveQuery extends from [[Query]], one can use query methods, such as [[where()]],
 * [[orderBy()]] to customize the query options.
 *
 * ActiveQuery also provides the following additional query options:
 *
 * - [[with()]]: list of relations that this query should be performed with.
 * - [[indexBy()]]: the name of the column by which the query result should be indexed.
 * - [[asArray()]]: whether to return each record as an array.
 *
 * These options can be configured using methods of the same name. For example:
 *
 * ~~~
 * $customers = Customer::find()->with('orders')->asArray()->all();
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class QuerySet extends Query
{
    private $_separator = '__';

    /**
     * @var string the name of the ActiveRecord class.
     */
    public $modelClass;
    /**
     * @var array a list of relations that this query should be performed with
     */
    public $with;
    /**
     * @var boolean whether to return each record as an array. If false (default), an object
     * of [[modelClass]] will be created to represent each record.
     */
    public $asArray;

    /**
     * @var string the SQL statement to be executed for retrieving AR records.
     * This is set by [[QuerySet::createCommand()]].
     */
    public $sql;

    /**
     * Model received from Manager
     * @var \Mindy\Orm\Model
     */
    public $model;

    /**
     * Executes query and returns all results as an array.
     * @param Connection $db the DB connection used to create the DB command.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all($db = null)
    {
        $command = $this->createCommand($db);
        $rows = $command->queryAll();
        if (!empty($rows)) {
            $models = $this->createModels($rows);
            return $models;
        } else {
            return [];
        }
    }

    /**
     * Executes query and returns a single row of result.
     * @param null $db
     * @return null|Orm
     */
    public function get($db = null)
    {
        $command = $this->createCommand($db);
        $row = $command->queryOne();
        if ($row !== false) {
            if ($this->asArray) {
                $model = $row;
            } else {
                /** @var Orm $class */
                $class = $this->modelClass;
                $model = $class::create($row);
            }
            if (!empty($this->with)) {
                $models = [$model];
                $this->findWith($this->with, $models);
                $model = $models[0];
            }
            return $model;
        } else {
            return null;
        }
    }

    /**
     * Creates a DB command that can be used to execute this query.
     * @param \Mindy\Query\Connection $db the DB connection used to create the DB command.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return \Mindy\Query\Command the created DB command instance.
     */
    public function createCommand($db = null)
    {
        /** @var Orm $modelClass */
        $modelClass = $this->modelClass;
        if ($db === null) {
            $db = $modelClass::getConnection();
        }

        $select = $this->select;
        $from = $this->from;

        if ($this->from === null) {
            $tableName = $modelClass::tableName();
            if ($this->select === null && !empty($this->join)) {
                $this->select = ["$tableName.*"];
            }
            $this->from = [$tableName];
        }
        list ($sql, $params) = $db->getQueryBuilder()->build($this);

        $this->select = $select;
        $this->from = $from;
        return $db->createCommand($sql, $params);
    }


    /**
     * @return mixed|null
     */
    public function retreivePrimaryKey()
    {
        return $this->model->getPkName();
    }

    public function buildCondition(array $query, $method, $queryCondition = [])
    {
        foreach($query as $name => $value) {
            if(is_numeric($name)) {
                throw new InvalidArgumentException('Keys in $q must be a string');
            }

            if(substr_count($name, $this->_separator) > 1) {
                // recursion
                throw new \Exception("Not implemented");

            } elseif (strpos($name, $this->_separator) !== false) {
                if($name === 'pk') {
                    $name = $this->retreivePrimaryKey();
                }

                list($field, $condition) = explode($this->_separator, $name);

                switch($condition) {
                    case 'isnull':
                    case 'exact':
                        $queryCondition = array_merge($queryCondition, [$field => $value]);
                        break;
                    case 'in':
                        $queryCondition = array_merge($queryCondition, ['in', $field, $value]);
                        break;
                    case 'gte':
                        $queryCondition = array_merge($queryCondition, ["$field >= :$field", [":$field" => $value]]);
                        break;
                    case 'gt':
                        $queryCondition = array_merge($queryCondition, ["$field > :$field", [":$field" => $value]]);
                        break;
                    case 'lte':
                        $queryCondition = array_merge($queryCondition, ["$field <= :$field", [":$field" => $value]]);
                        break;
                    case 'lt':
                        $queryCondition = array_merge($queryCondition, ["$field < :$field", [":$field" => $value]]);
                        break;
                    case 'icontains':
                        // TODO ILIKE
                        // TODO see buildLikeCondition in QueryBuilder. ILIKE? WAT? QueryBuilder dont known about ILIKE
                        break;
                    case 'contains':
                        $queryCondition = array_merge($queryCondition, ['like', $field, $value]);
                        break;
                    case 'startswith':
                        $queryCondition = array_merge($queryCondition, ['like', $field, '%' . $value]);
                        break;
                    case 'istartswith':
                        // TODO ILIKE something%
                        // TODO see buildLikeCondition in QueryBuilder. ILIKE? WAT? QueryBuilder dont known about ILIKE
                        break;
                    case 'endswith':
                        $queryCondition = array_merge($queryCondition, ['like', $field, $value . '%']);
                        break;
                    case 'iendswith':
                        // TODO ILIKE %something
                        // TODO see buildLikeCondition in QueryBuilder. ILIKE? WAT? QueryBuilder dont known about ILIKE
                        break;
                    case 'range':
                        if(count($value) != 2) {
                            throw new InvalidArgumentException('Range condition must be a array of 2 elements. Example: something__range=[1, 2]');
                        }
                        $queryCondition = array_merge($queryCondition, array_merge(['between', $field], $value));
                        break;
                    case 'year':
                        // TODO BETWEEN
                        // Entry.objects.filter(pub_date__year=2005)
                        // SELECT ... WHERE pub_date BETWEEN '2005-01-01' AND '2005-12-31';
                        break;
                    case 'month':
                        // TODO
                        // Entry.objects.filter(pub_date__month=12)
                        // SELECT ... WHERE EXTRACT('month' FROM pub_date) = '12';
                        break;
                    case 'day':
                        // TODO
                        break;
                    case 'week_day':
                        // TODO
                        // Entry.objects.filter(pub_date__week_day=2)
                        // No equivalent SQL code fragment is included for this lookup because implementation of
                        // the relevant query varies among different database engines.
                        break;
                    case 'hour':
                        // TODO
                        // Event.objects.filter(timestamp__hour=23)
                        // SELECT ... WHERE EXTRACT('hour' FROM timestamp) = '23';
                        break;
                    case 'minute':
                        // TODO
                        // Event.objects.filter(timestamp__minute=29)
                        // SELECT ... WHERE EXTRACT('minute' FROM timestamp) = '29';
                        break;
                    case 'second':
                        // TODO
                        // Event.objects.filter(timestamp__second=31)
                        // SELECT ... WHERE EXTRACT('second' FROM timestamp) = '31';
                        break;
                    case 'search':
                        // TODO
                        // Entry.objects.filter(headline__search="+Django -jazz Python")
                        // SELECT ... WHERE MATCH(tablename, headline) AGAINST (+Django -jazz Python IN BOOLEAN MODE);
                        break;
                    case 'regex':
                        // TODO
                        // Entry.objects.get(title__regex=r'^(An?|The) +')
                        // SELECT ... WHERE title REGEXP BINARY '^(An?|The) +'; -- MySQL
                        // SELECT ... WHERE REGEXP_LIKE(title, '^(an?|the) +', 'c'); -- Oracle
                        // SELECT ... WHERE title ~ '^(An?|The) +'; -- PostgreSQL
                        // SELECT ... WHERE title REGEXP '^(An?|The) +'; -- SQLite
                        break;
                    case 'iregex':
                        // TODO
                        // SELECT ... WHERE title REGEXP '^(an?|the) +'; -- MySQL
                        // SELECT ... WHERE REGEXP_LIKE(title, '^(an?|the) +', 'i'); -- Oracle
                        // SELECT ... WHERE title ~* '^(an?|the) +'; -- PostgreSQL
                        // SELECT ... WHERE title REGEXP '(?i)^(an?|the) +'; -- SQLite
                        break;
                    default:
                        break;
                }

            } else {
                if($name === 'pk') {
                    $name = $this->retreivePrimaryKey();
                }

                $queryCondition = array_merge($queryCondition, [$name => $value]);
            }
        }

        $this->$method($queryCondition);

        return $this;
    }

    public function filter(array $query)
    {
        return $this->buildCondition($query, 'andWhere');
    }

    public function orFilter(array $query)
    {
        return $this->buildCondition($query, 'orWhere');
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
     * Converts found rows into model instances
     * @param array $rows
     * @return array|ActiveRecord[]
     */
    private function createModels($rows)
    {
        $models = [];
        if ($this->asArray) {
            if ($this->indexBy === null) {
                return $rows;
            }
            foreach ($rows as $row) {
                if (is_string($this->indexBy)) {
                    $key = $row[$this->indexBy];
                } else {
                    $key = call_user_func($this->indexBy, $row);
                }
                $models[$key] = $row;
            }
        } else {
            /** @var ActiveRecord $class */
            $class = $this->modelClass;
            if ($this->indexBy === null) {
                foreach ($rows as $row) {
                    $models[] = $class::create($row);
                }
            } else {
                foreach ($rows as $row) {
                    $model = $class::create($row);
                    if (is_string($this->indexBy)) {
                        $key = $model->{$this->indexBy};
                    } else {
                        $key = call_user_func($this->indexBy, $model);
                    }
                    $models[$key] = $model;
                }
            }
        }
        return $models;
    }

}
