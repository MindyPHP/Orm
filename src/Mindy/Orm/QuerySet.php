<?php
/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Mindy\Orm;

use Mindy\Query\Query;

class QuerySet extends Query
{
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

    /**
     * Counter of params
     * @var int
     */
    private $_paramsCount = 0;

    /**
     * Chains of joins
     * @var array
     */
    private $_chains = array();

    /**
     * Has chained
     * @var bool
     */
    private $_chainedHasMany = false;

    /**
     * Counter of joined tables aliases
     * @var int
     */
    private $_aliasesCount = 0;

    /**
     * Current table alias
     * @var string
     */
    public $tableAlias = 't1';

    /**
     * Executes query and returns all results as an array.
     * @param Connection $db the DB connection used to create the DB command.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all($db = null)
    {
        // @TODO: hardcode, refactoring
        $group = $this->groupBy;
        if ($this->_chainedHasMany && !$group) {
            $this->groupBy($this->tableAlias . '.' . $this->retreivePrimaryKey());
        }
        $command = $this->createCommand($db);
        $this->groupBy = $group;

        $rows = $command->queryAll();
        if (!empty($rows)) {
            $models = $this->createModels($rows);
            return $models;
        } else {
            return [];
        }
    }

    /**
     * Paginate models
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function paginate($page = 1, $pageSize = 10)
    {
        return $this->limit($pageSize)->offset($pageSize * $page);
    }

    public function allSql($db = null)
    {
        $group = $this->groupBy;
        if ($this->_chainedHasMany && !$group) {
            $this->groupBy($this->tableAlias . '.' . $this->retreivePrimaryKey());
        }
        $return = parent::allSql($db);
        $this->groupBy = $group;
        return $return;
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
            return $model;
        } else {
            return null;
        }
    }

    /**
     * @param null|string $q
     * @param null|object $db
     * @return int
     */
    public function count($q = null, $db = null)
    {
        if (!$q) {
            if ($this->_chainedHasMany) {
                $q = 'DISTINCT ' . $this->quoteColumnName($this->tableAlias . '.' . $this->retreivePrimaryKey());
            } else {
                $q = '*';
            }
        }
        return parent::count($q, $db);
    }

    /**
     * @param null|string $q
     * @param null|object $db
     * @return string
     */
    public function countSql($q = null, $db = null)
    {
        if (!$q) {
            if ($this->_chainedHasMany) {
                $q = 'DISTINCT ' . $this->quoteColumnName($this->tableAlias . '.' . $this->retreivePrimaryKey());
            } else {
                $q = '*';
            }
        }
        return parent::countSql($q, $db);
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

    /**
     * Add chained relation
     * @param array|string $key_chain
     * @param string $alias
     * @param object $model
     */
    protected function addChain($key_chain, $alias, $model)
    {
        if (is_array($key_chain))
            $key_chain = $this->prefixToKey($key_chain);

        $this->_chains[$key_chain] = array(
            'alias' => $alias,
            'model' => $model
        );
    }

    /**
     * Makes alias for joined table
     * @param $table
     * @return string
     */
    protected function makeAliasKey($table)
    {
        $this->_aliasesCount += 1;
        return strtolower($table) . '_' . $this->_aliasesCount;
    }

    /**
     * Makes key for param
     * @param $fieldName
     * @return string
     */
    protected function makeParamKey($fieldName)
    {
        $this->_paramsCount += 1;
        return $fieldName . $this->_paramsCount;
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

        $prefix_remains = array();
        $chain_remains = array();

        foreach ($prefix as $relation_name) {
            $chain[] = $relation_name;
            if ($founded = $this->getChain($chain)) {
                $model = $founded['model'];
                $alias = $founded['alias'];
                $prefix_remains = array();
                $chain_remains = $chain;
            } else {
                $prefix_remains[] = $relation_name;
            }
        }

        return [$model, $alias, $prefix_remains, $chain_remains];
    }

    /**
     * Makes connection by chain (creates joins)
     * @param $prefix
     */
    protected function makeChain($prefix)
    {
        // Searching closest already connected relation
        list($model, $alias, $prefix, $chain) = $this->searchChain($prefix);

        foreach ($prefix as $relation_name) {
            $chain[] = $relation_name;
            $related_value = $model->getField($relation_name);
            list($relatedModel, $joinTables) = $related_value->getJoin();

            foreach ($joinTables as $join) {
                $type = isset($join['type']) ? $join['type'] : 'LEFT JOIN';
                $new_alias = $this->makeAliasKey($join['table']);
                $table = $join['table'] . ' ' . $new_alias;

                $from = $alias . '.' . $join['from'];
                $to = $new_alias . '.' . $join['to'];
                $on = $this->quoteColumnName($from) . ' = ' . $this->quoteColumnName($to);

                $this->join($type, $table, $on);

                // Has many relations (we must work only with current model lines - exclude duplicates)
                if (isset($join['group']) && ($join['group']) && !$this->_chainedHasMany) {
                    $this->_chainedHasMany = true;
                }

                $alias = $new_alias;
            }

            $this->addChain($chain, $alias, $relatedModel);

            $model = $relatedModel;
        }
    }

    /**
     * Returns chain if exists
     * @param array|string $key_chain
     * @return null|array
     */
    protected function getChain($key_chain)
    {
        if (is_array($key_chain))
            $key_chain = $this->prefixToKey($key_chain);

        if (isset($this->_chains[$key_chain])) {
            return $this->_chains[$key_chain];
        }
        return null;
    }

    /**
     * Returns chain alias
     * @param array|string $key_chain
     * @return string
     */
    protected function getChainAlias($key_chain)
    {
        return ($chain = $this->getChain($key_chain)) ? $chain['alias'] : '';
    }

    /**
     * Get or create alias and related model by chain
     * @param array $prefix
     * @return array
     */
    protected function getOrCreateChainAlias(array $prefix)
    {
        if (count($prefix) > 0) {

            if (!$this->from) {
                $this->from($this->model->tableName() . ' ' . $this->tableAlias);
                $this->select($this->tableAlias . '.*');
            }

            if (!($chain = $this->getChain($prefix))) {
                $this->makeChain($prefix);
                $chain = $this->getChain($prefix);
            }

            if ($chain) {
                return [$chain['alias'], $chain['model']];
            }
        }

        return ['', $this->model];
    }

    protected function parseLookup(array $query, array $queryCondition = [], array $queryParams = [])
    {
        // $user = User::objects()->get(['pk' => 1]);
        // $pages = Page::object()->filter(['user__in' => [$user]])->all();
        $lookup = new LookupBuilder($query);
        $lookup_query = [];
        $lookup_params = [];

        foreach ($lookup->parse() as $data) {
            list($prefix, $field, $condition, $params) = $data;
            list($alias, $model) = $this->getOrCreateChainAlias($prefix);

            if ($field === 'pk') {
                $field = $model->getPkName();
            }

            if ($alias) {
                $field = $alias . '.' . $field;
            }

            if (is_object($params) && get_class($params) == __CLASS__ && $condition != 'in') {
                throw new Exception("QuerySet object can be used as a parameter only in case of 'in' condition");
            }

            $method = 'build' . ucfirst($condition);

            list($query, $params) = $this->$method($field, $params);
            $lookup_params = array_merge($lookup_params, $params);
            $lookup_query[] = $query;
        }

        return [$lookup_query, $lookup_params];
    }

    public function buildExact($field, $value)
    {
        return [[$field => $value], []];
    }

    public function buildIsnull($field, $value)
    {
        if ($value) {
            return [[$field => null], []];
        } else {
            return [['not', [$field => null]], []];
        }
    }

    public function buildIn($field, $value)
    {
        if (is_object($value) && get_class($value) == __CLASS__) {
            return [['and', $this->quoteColumnName($field) . ' IN (' . $value->allSql() . ')'], []];
        }

        return [['in', $field, $value], []];
    }

    public function buildGte($field, $value)
    {
        $paramName = $this->makeParamKey($field);
        return [['and', $this->quoteColumnName($field) . ' >= :' . $paramName], [':' . $paramName => $value]];
    }

    public function buildGt($field, $value)
    {
        $paramName = $this->makeParamKey($field);
        return [['and', $this->quoteColumnName($field) . ' > :' . $paramName], [':' . $paramName => $value]];
    }

    public function buildLte($field, $value)
    {
        $paramName = $this->makeParamKey($field);
        return [['and', $this->quoteColumnName($field) . ' <= :' . $paramName], [':' . $paramName => $value]];
    }

    public function buildLt($field, $value)
    {
        $paramName = $this->makeParamKey($field);
        return [['and', $this->quoteColumnName($field) . ' < :' . $paramName], [':' . $paramName => $value]];
    }

    public function buildContains($field, $value)
    {
        return [['like', $field, $value], []];
    }

    public function buildIcontains($field, $value)
    {
        return [['ilike', $field, $value], []];
    }

    public function buildStartswith($field, $value)
    {
        return [['like', $field, $value . '%', false], []];
    }

    public function buildIStartswith($field, $value)
    {
        return [['ilike', $field, $value . '%', false], []];
    }

    public function buildEndswith($field, $value)
    {
        return [['like', $field, '%' . $value, false], []];
    }

    public function buildIendswith($field, $value)
    {
        return [['ilike', $field, '%' . $value, false], []];
    }

    public function buildRange($field, $value)
    {
        list($start, $end) = $value;
        return [['between', $field, $start, $end], []];
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
     * @return array|Orm[]
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
            /** @var Orm $class */
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
    public function quoteColumnName($name, $db = null)
    {
        if (!$db)
            $db = $this->getDb();
        return $db->quoteColumnName($name);
    }

    /**
     *
     * @param $columns
     * @return array
     */
    protected function normalizeOrderBy($columns)
    {
        if (!is_array($columns)) {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        }
        $result = [];
        foreach ($columns as $column) {
            $sort = SORT_ASC;

            if (substr($column, 0, 1) == '-') {
                $column = substr($column, 1);
                $sort = SORT_DESC;
            }

            $builder = new LookupBuilder();
            list($prefix, $field, $condition, $params) = $builder->parseLookup($column);
            list($alias, $model) = $this->getOrCreateChainAlias($prefix);

            if ($alias) {
                $column = $alias . '.' . $field;
            }
            $result[$column] = $sort;
        }
        return $result;
    }

    /**
     * Order by alias
     * @param $columns
     * @return static
     */
    public function order($columns)
    {
        return $this->orderBy($columns);
    }


    /**
     * 'id' -> '`t1`.`id`'
     * @param $column
     * @return string
     */
    public function aliasColumn($column){
        $builder = new LookupBuilder();
        list($prefix, $field, $condition, $params) = $builder->parseLookup($column);
        list($alias, $model) = $this->getOrCreateChainAlias($prefix);

        $column = $field;
        if ($alias){
            $column = $alias . '.' . $column;
        }elseif ($this->_chainedHasMany){
            $column = $this->tableAlias . '.' . $column;
        }
        return $this->quoteColumnName($column);
    }

    /**
     * Converts string to float or val
     * @param $value
     * @return float|int
     */
    public function numval($value)
    {
        if (strpos($value, '.') !== false) {
            return floatval($value);
        } else {
            return intval($value);
        }
    }

    /**
     * @param string $column
     * @param null $db
     * @return float|int
     */
    public function sum($column, $db = null)
    {
        $column = $this->aliasColumn($column);
        $value = parent::sum($column, $db);
        return $this->numval($value);
    }

    /**
     * @param string $column
     * @param null $db
     * @return float|int
     */
    public function average($column, $db = null)
    {
        $column = $this->aliasColumn($column);
        $value = parent::average($column, $db);
        return $this->numval($value);
    }

    /**
     * @param string $column
     * @param null $db
     * @return float|int
     */
    public function min($column, $db = null)
    {
        $column = $this->aliasColumn($column);
        $value = parent::min($column, $db);
        return $this->numval($value);
    }

    /**
     * @param string $column
     * @param null $db
     * @return float|int
     */
    public function max($column, $db = null)
    {
        $column = $this->aliasColumn($column);
        $value = parent::max($column, $db);
        return $this->numval($value);
    }
}
