<?php

namespace Mindy\Orm;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Mindy\Exception\Exception;
use Mindy\Orm\Exception\MultipleObjectsReturned;
use Mindy\Orm\Exception\ObjectDoesNotExist;
use Mindy\Query\Query;
use Serializable;
use Traversable;

class QuerySet extends Query implements Iterator, ArrayAccess, Countable, Serializable
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

    protected $_tableAlias;

    /**
     * @var bool
     */
    private $_filterComplete = false;
    /**
     * @var array
     */
    private $_filterAnd = [];
    /**
     * @var array
     */
    private $_filterOr = [];
    /**
     * @var array
     */
    private $_filterExclude = [];
    /**
     * @var array
     */
    private $_filterOrExclude = [];

    protected function prepareCommand($db = null)
    {
        $this->prepareConditions();

        // @TODO: hardcode, refactoring
        $group = $this->groupBy;
        if ($this->_chainedHasMany && !$group) {
            $this->groupBy($this->quoteColumnName($this->tableAlias . '.' . $this->retreivePrimaryKey()));
        }
        $command = $this->createCommand($db);
        $this->groupBy = $group;
        $this->setCommand($command);
        return $this;
    }

    /**
     * Executes query and returns all results as an array.
     * @param Connection $db the DB connection used to create the DB command.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all($db = null)
    {
        return $this->prepareCommand($db)->getData($this->asArray ? false : true);

//        $rows = $command->queryAll();
//        if (!empty($rows)) {
//            return $this->createModels($rows);
//        } else {
//            return [];
//        }
    }

    public function getTableAlias()
    {
        if (!$this->_tableAlias) {
            $table_name = $this->model->tableName();
            $table_name = $this->makeAliasKey($table_name);
            $this->_tableAlias = $table_name;
        }
        return $this->_tableAlias;
    }

    /**
     * @param array $fieldsList
     * @param bool $flat
     * @return array
     */
    public function valuesList(array $fieldsList = [], $flat = false)
    {
        // @TODO: hardcode, refactoring
        $select = $this->select;

        $group = $this->groupBy;
        if ($this->_chainedHasMany && !$group) {
            $this->groupBy($this->quoteColumnName($this->tableAlias . '.' . $this->retreivePrimaryKey()));
        }

        $valuesSelect = [];
        foreach ($fieldsList as $fieldName) {
            $valuesSelect[] = $this->aliasColumn($fieldName) . ' AS ' . $fieldName;
        }
        $this->select = $valuesSelect;

        $rows = $this->createCommand()->queryAll();

        $this->groupBy = $group;
        $this->select = $select;

        if ($flat) {
            $flatArr = [];
            foreach($rows as $item) {
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
        $this->prepareConditions(false);
        return parent::updateAll($this->model->tableName(), $attributes, $this->model->getConnection());
    }

    public function updateSql(array $attributes)
    {
        $this->prepareConditions(false);
        $command = $this->createCommand($this->model->getConnection());
        $command->update($this->model->tableName(), $attributes, $this->where, $this->params);
        return $command->sql;
    }

    public function updateCounters(array $counters)
    {
        $table = $this->model->tableName() . ' ' . $this->getTableAlias();
        return parent::updateCountersInternal($table, $this->makeAliasAttributes($counters), $this->model->getConnection());
    }

    public function getOrCreate(array $attributes)
    {
        $model = $this->filter($attributes)->get();
        if ($model === null) {
            $model = $this->model;
            $model->setAttributes($attributes);
            $model->save();
        }

        return $model;
    }

    public function updateOrCreate(array $attributes, array $updateAttributes)
    {
        $model = $this->filter($attributes)->get();
        if ($model) {
            $model->setAttributes($updateAttributes);
        } else {
            $model = $this->model;
            $model->setAttributes($updateAttributes);
        }
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
        $this->limit($pageSize)->offset($page > 1 ? $pageSize * ($page - 1) : 0);
        return $this;
    }

    public function allSql($db = null)
    {
        $this->prepareConditions();

        $group = $this->groupBy;
        if ($this->_chainedHasMany && !$group) {
            $this->groupBy($this->quoteColumnName($this->tableAlias . '.' . $this->retreivePrimaryKey()));
        }
        $return = parent::allSql($db);
        $this->groupBy = $group;
        return $return;
    }

    protected function prepareConditions($aliased = true)
    {
        if($this->_filterComplete === false) {
            foreach($this->_filterAnd as $query) {
                $this->buildCondition($query, 'andWhere', ['and'], $aliased);
            }

            foreach($this->_filterOr as $query) {
                $this->buildCondition($query, 'orWhere', ['and'], $aliased);
            }

            foreach($this->_filterExclude as $query) {
                $this->buildCondition($query, 'excludeWhere', ['and'], $aliased);
            }

            foreach($this->_filterOrExclude as $query) {
                $this->buildCondition($query, 'excludeOrWhere', ['and'], $aliased);
            }
            $this->_filterComplete = true;
        }

        return $this;
    }

    /**
     * @param null $db
     * @return string
     */
    public function getSql()
    {
        $this->prepareConditions();
        return parent::getSql();
    }

    /**
     * Executes query and returns a single row of result.
     * @throws \Mindy\Orm\Exception\MultipleObjectsReturned
     * @return null|Orm
     */
    public function get()
    {
        $this->prepareConditions();
        $rows = $this->createCommand()->queryAll();
        if (count($rows) > 1) {
            throw new MultipleObjectsReturned();
        } elseif (count($rows) === 0) {
            return null;
        }
        return $this->asArray ? array_shift($rows) : $this->createModel(array_shift($rows));
    }

    /**
     * @param null|string $q
     * @param null|object $db
     * @return int
     */
    public function countInternal($q = null)
    {
        $this->prepareConditions();
        if (!$q) {
            if ($this->_chainedHasMany) {
                $q = 'DISTINCT ' . $this->quoteColumnName($this->tableAlias . '.' . $this->retreivePrimaryKey());
            } else {
                $q = '*';
            }
        }
        return parent::count($q);
    }

    /**
     * @param null|string $q
     * @param null|object $db
     * @return string
     */
    public function countSql($q = null)
    {
        $this->prepareConditions();
        if (!$q) {
            if ($this->_chainedHasMany) {
                $q = 'DISTINCT ' . $this->quoteColumnName($this->tableAlias . '.' . $this->retreivePrimaryKey());
            } else {
                $q = '*';
            }
        }
        return parent::countSql($q);
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
     * @return \Mindy\Query\QueryBuilder|\Mindy\Query\Mysql\QueryBuilder|\Mindy\Query\Sqlite\QueryBuilder|\Mindy\Query\Pgsql\QueryBuilder|\Mindy\Query\Oci\QueryBuilder|\Mindy\Query\Cubrid\QueryBuilder|\Mindy\Query\Mssql\QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->getDb()->getQueryBuilder();
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
        $table = str_replace(['{{', '}}', '%', '`'], '', $table);
        $table = strtolower($table) . '_' . $this->_aliasesCount;
        return $this->quoteColumnName($table);
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
        if (!$this->from) {
            $this->from($this->model->tableName() . ' ' . $this->tableAlias);
            $this->select($this->tableAlias . '.*');
        }

        if (count($prefix) > 0) {
            if (!($chain = $this->getChain($prefix))) {
                $this->makeChain($prefix);
                $chain = $this->getChain($prefix);
            }

            if ($chain) {
                return [$chain['alias'], $chain['model']];
            }
        }

        return [$this->tableAlias, $this->model];
    }

    /**
     * Example:
     * >>> $user = User::objects()->get(['pk' => 1]);
     * >>> $pages = Page::object()->filter(['user__in' => [$user]])->all();
     * @param array $query
     * @throws Exception
     * @return array
     */
    protected function parseLookup(array $query, $aliased = true)
    {
        $queryBuilder = $this->getQueryBuilder();

        $lookup = new LookupBuilder($query);
        $lookup_query = [];
        $lookup_params = [];

        foreach ($lookup->parse() as $data) {
            list($prefix, $field, $condition, $params) = $data;
            list($alias, $model) = $this->getOrCreateChainAlias($prefix);

            if ($field === 'pk') {
                $field = $model->getPkName();
            }

            if (is_object($params) && get_class($params) == __CLASS__) {
                if($condition != 'in') {
                    throw new Exception("QuerySet object can be used as a parameter only in case of 'in' condition");
                } else {
                    $params->prepareConditions();
                }
            }

            // https://github.com/studio107/Mindy_Orm/issues/26
            if ($model->hasField($field)) {
                if ($condition == 'in' || $condition == 'exact') {
                    $initField = $model->getField($field);
                    if (is_a($initField, $model::$foreignField)) {
                        $initFieldModelClass = $initField->modelClass;
                        $field .= '_' . $initFieldModelClass::primaryKeyName();

                        // https://github.com/studio107/Mindy_Orm/issues/29
                        if ($condition == 'exact') {
                            if (is_a($params, Model::className())) {
                                $params = $params->pk;
                            }
                        }
                    }
                }
            }

            if($aliased) {
                if (strpos($field, '.') === false) {
                    if ($alias) {
                        $field = $alias . '.' . $field;
                    }
                }
            }

            $method = 'build' . ucfirst($condition);

            if (method_exists($this, $method)) {
                list($query, $params) = $this->$method($field, $params);
            } else {
                list($query, $params) = $queryBuilder->$method($field, $params);
            }
            $lookup_params = array_merge($lookup_params, $params);
            $lookup_query[] = $query;
        }

        return [$lookup_query, $lookup_params];
    }

    /**
     * @param array $query
     * @param $method
     * @param array $queryCondition
     * @return $this
     */
    public function buildCondition(array $query, $method, $queryCondition = [], $aliased = true)
    {
        list($condition, $params) = $this->parseLookup($query, $aliased);
        $this->$method(array_merge($queryCondition, $condition), $params);

        return $this;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function filter(array $query)
    {
        $this->_filterAnd[] = $query;
        return $this;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function orFilter(array $query)
    {
        $this->_filterOr[] = $query;
        return $this;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function exclude(array $query)
    {
        $this->_filterExclude[] = $query;
        return $this;
//        return $this->buildCondition($query, 'excludeWhere', ['and']);
    }

    /**
     * @param array $query
     * @return $this
     */
    public function orExclude(array $query)
    {
        $this->_filterOrExclude[] = $query;
        return $this;
//        return $this->buildCondition($query, 'excludeOrWhere', ['and']);
    }

    /**
     * @param $condition
     * @param array $params
     * @return static
     */
    public function excludeWhere($condition, $params = [])
    {
        $condition = ['not', $condition];
        return parent::andWhere($condition, $params);
    }

    /**
     * @param $condition
     * @param array $params
     * @return static
     */
    public function excludeOrWhere($condition, $params = [])
    {
        $condition = ['not', $condition];
        return parent::orWhere($condition, $params);
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
//    private function createModels($rows)
//    {
//        $models = [];
//        if ($this->asArray) {
//            if ($this->indexBy === null) {
//                return $rows;
//            }
//            foreach ($rows as $row) {
//                if (is_string($this->indexBy)) {
//                    $key = $row[$this->indexBy];
//                } else {
//                    $key = call_user_func($this->indexBy, $row);
//                }
//                $models[$key] = $row;
//            }
//        } else {
//            /** @var Orm $class */
//            $class = $this->modelClass;
//            if ($this->indexBy === null) {
//                foreach ($rows as $row) {
//                    $models[] = $class::create($row);
//                }
//            } else {
//                foreach ($rows as $row) {
//                    $model = $class::create($row);
//                    if (is_string($this->indexBy)) {
//                        $key = $model->{$this->indexBy};
//                    } else {
//                        $key = call_user_func($this->indexBy, $model);
//                    }
//                    $models[$key] = $model;
//                }
//            }
//        }
//        return $models;
//    }

    /**
     * Converts name => `name`, user.name => `user`.`name`
     * @param string $name Column name
     * @param object|null $db Connection
     * @return string Quoted column name
     */
    public function quoteColumnName($name, $db = null)
    {
        if (!$db) {
            $db = $this->getDb();
        }
        return $db->quoteColumnName($name);
    }

    /**
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

            $column = $this->aliasColumn($column);
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
    public function aliasColumn($column)
    {
        $builder = new LookupBuilder();
        list($prefix, $field, $condition, $params) = $builder->parseLookup($column);
        list($alias, $model) = $this->getOrCreateChainAlias($prefix);

        $column = $field;

        if (strpos($column, '.') === false) {
            if ($alias) {
                $column = $alias . '.' . $column;
            } elseif ($this->_chainedHasMany) {
                $column = $this->tableAlias . '.' . $column;
            }
        }
        return $this->quoteColumnName($column);
    }

    /**
     * Make aliased attributes
     * @param array $attributes
     * @return array new attributes with table aliases
     */
    protected function makeAliasAttributes(array $attributes)
    {
        $new = [];
        foreach ($attributes as $key => $value) {
            $new[$this->getTableAlias() . '.' . $key] = $value;
        }
        return $new;
    }

    /**
     * Converts string to float or int
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
        $this->prepareConditions();
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
        $this->prepareConditions();
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
        $this->prepareConditions();
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
        $this->prepareConditions();
        $column = $this->aliasColumn($column);
        $value = parent::max($column, $db);
        return $this->numval($value);
    }

    public function delete($db = null)
    {
        $this->prepareConditions();
        $alias = $this->getTableAlias();
        $tableName = $alias . " USING " . $this->model->tableName() . " AS " . $alias;
        return $this->createCommand($db)->delete($tableName, $this->where, $this->params)->execute();
    }

    /********************************************************
     * Iterators
     ********************************************************/

    /**
     * @var null
     */
    protected $_data = [];

    /**
     * @var \Mindy\Query\Command
     */
    protected $command;

    /**
     * @param $command \Mindy\Query\Command
     * @return $this
     */
    protected function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }

    protected function createModel($row)
    {
        $className = $this->modelClass;
        /** @var $record Model */
        $record = new $className;
        $record->setAttributes($row);
        $record->setOldAttributes($row);
        return $record;
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
    public function getData($forceModels = false)
    {
        if(empty($this->_data)) {
            if($this->command === null) {
                $this->prepareCommand();
            }
            $this->_data = $this->command->queryAll();
            $this->command = null;
        }
        return $forceModels ? $this->createModels($this->_data) : $this->_data;
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
    public function count($q = '*')
    {
        if(empty($this->_data)) {
            // TODO return $this->countInternal($q);
            $asArray = $this->asArray;
            $cnt = count($this->asArray()->all());
            $this->asArray = $asArray;
            return $cnt;
        } else {
            return count($this->_data);
        }
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return Model[]
     */
    public function unserialize($serialized)
    {
        return $this->createModels(unserialize($serialized));
    }
}
