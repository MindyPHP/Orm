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
     * Model receive
     * @var \Mindy\Orm\Model
     */
    public $model;

    private $_params_count = 0;

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

    protected function getChainedQuerySet(array $prefix)
    {
        $qs = null;
        if(count($prefix) > 0) {
            foreach($prefix as $chain) {
                if($qs === null) {
                    $qs = $this->$chain;
                } else {
                    $qs = $qs->$chain;
                }
            }
        } else {
            $qs = $this;
        }

        return $qs;
    }

    protected function parseLookup(array $query, array $queryCondition = [], array $queryParams = [])
    {
        // $user = User::objects()->get(['pk' => 1]);
        // $pages = Page::object()->filter(['user__in' => [$user]])->all();

        $lookup = new LookupBuilder($query);
        $lookup_query = [];
        $lookup_params = [];

        foreach($lookup->parse() as $data) {
            list($prefix, $field, $condition, $params) = $data;

            $qs = $this->getChainedQuerySet($prefix);

            if($field === 'pk') {
                $field = $this->retreivePrimaryKey();
            }

            $method = 'build' . ucfirst($condition);
            list($query, $params) = $qs->$method($field, $params);
            $lookup_params = array_merge($lookup_params, $params);
            $lookup_query[] = $query;
        }

        return [$lookup_query, $lookup_params];
    }

    public function buildExact($field, $value)
    {
        return [[$field => $value],[]];
    }

    public function buildIn($field, $value)
    {
        return [['in', $field, $value], []];
    }

    public function buildGte($field, $value)
    {
        $paramName = $this->generateParamName($field);
        return [['and', $this->quoteColumnName($field) . ' >= :' . $paramName], [':' . $paramName => $value]];
    }

    public function buildGt($field, $value)
    {
        $paramName = $this->generateParamName($field);
        return [['and', $this->quoteColumnName($field) . ' > :' . $paramName], [':' . $paramName => $value]];
    }

    public function buildLte($field, $value)
    {
        $paramName = $this->generateParamName($field);
        return [['and', $this->quoteColumnName($field) . ' <= :' . $paramName], [':' . $paramName => $value]];
    }

    public function buildLt($field, $value)
    {
        $paramName = $this->generateParamName($field);
        return [['and', $this->quoteColumnName($field) . ' < :' . $paramName], [':' . $paramName => $value]];
    }

    public function buildContains($field, $value)
    {
        return [['like', $field, $value],[]];
    }

    public function buildIcontains($field, $value)
    {
        return [['ilike', $field, $value],[]];
    }

    public function buildStartswith($field, $value)
    {
        return [['like', $field, '%' . $value, false],[]];
    }

    public function buildIStartswith($field, $value)
    {
        return [['ilike', $field, '%' . $value, false],[]];
    }

    public function buildEndswith($field, $value)
    {
        return [['like', $field, $value . '%', false],[]];
    }

    public function buildIendswith($field, $value)
    {
        return [['ilike', $field, $value . '%', false],[]];
    }

    public function buildRange($field, $value)
    {
        list($start, $end) = $value;
        return [['between', $field, $start, $end],[]];
    }

    public function buildCondition(array $query, $method, $queryCondition = [])
    {
        list($condition, $params) = $this->parseLookup($query);
        $this->$method(array_merge($queryCondition, $condition), $params);

        return $this;
    }

    public function filter(array $query)
    {
        return $this->buildCondition($query, 'andWhere', ['and']);
    }

    public function orFilter(array $query)
    {
        return $this->buildCondition($query, 'orWhere', ['and']);
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

    /**
     * Converts name => `name`, user.name => `user`.`name`
     * @param string $name Column name
     * @param object|null $db Connection
     * @return string Quoted column name
     */
    public function quoteColumnName($name, $db = null){
        if (!$db)
            $db = $this->getDb();
        return $db->quoteColumnName($name);
    }

    public function generateParamName($fieldName){
        $this->_params_count += 1;
        return $fieldName . $this->_params_count;
    }
}
