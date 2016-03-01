<?php

namespace Mindy\Orm;

use Mindy\Exception\Exception;
use Mindy\Helper\Creator;
use Mindy\Orm\Exception\MultipleObjectsReturned;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Q\Q;
use Modules\AltWork\Models\Issue;

/**
 * Class QuerySet
 * @package Mindy\Orm
 */
class QuerySet extends QuerySetBase
{
    /**
     * @var null
     */
    protected $_data = [];
    /**
     * @var \Mindy\Query\Command
     */
    protected $command;
    /**
     * @var array a list of relations that this query should be performed with
     */
    public $with = [];
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
    private $_chains = [];
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
     * @var string
     */
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

    private static $_cache;

    public static function getCache()
    {
        if (self::$_cache === null) {
            if (class_exists('\Mindy\Base\Mindy')) {
                self::$_cache = \Mindy\Base\Mindy::app()->getComponent('cache');
            } else {
                self::$_cache = new \Mindy\Cache\DummyCache;
            }
        }
        return self::$_cache;
    }

    /**
     * @return $this
     */
    protected function prepareCommand()
    {
        $this->prepareConditions();

        // @TODO: hardcode, refactoring
//        $group = $this->groupBy;
//        if ($this->_chainedHasMany && !$group && $group !== false) {
//            $this->groupBy($this->quoteColumnName($this->tableAlias) . '.' . $this->quoteColumnName($this->retreivePrimaryKey()));
//        }
        $command = $this->createCommand();
//        $this->groupBy = $group;
        $this->setCommand($command);
        return $this;
    }

    public function join($type, $table, $on = '', $params = [])
    {
        $query = parent::join($type, $table, $on, $params);
        if ($this->_chainedHasMany) {
            $this->groupBy($this->quoteColumnName($this->tableAlias) . '.' . $this->quoteColumnName($this->retreivePrimaryKey()));
        }
        return $query;
    }

    /**
     * Executes query and returns all results as an array.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all()
    {
        return $this->getData();
    }

    /**
     * @param int $batchSize
     * @return \Mindy\Orm\BatchDataIterator
     */
    public function batch($batchSize = 100)
    {
        $this->prepareConditions();
        return Creator::createObject([
            'class' => BatchDataIterator::className(),
            'qs' => $this,
            'batchSize' => $batchSize,
            'db' => $this->getDb(),
            'each' => false,
            'asArray' => $this->asArray,
        ]);
    }

    public function getTableAlias()
    {
        if (!$this->_tableAlias) {
            $this->_tableAlias = $this->makeAliasKey($this->model->tableName());
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
//        if ($this->_chainedHasMany && !$group) {
//            $this->groupBy($this->quoteColumnName($this->tableAlias) . '.' . $this->quoteColumnName($this->retreivePrimaryKey()));
//        }

        $valuesSelect = [];
        foreach ($fieldsList as $name) {
            if ($name == 'pk') {
                $name = $this->retreivePrimaryKey();
            }
            $valuesSelect[] = $this->aliasColumn($name) . ' AS ' . $name;
        }
        $this->select = $valuesSelect;

        $rows = $this->asArray()->all();

        $this->groupBy = $group;
        $this->select = $select;

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
        $this->prepareConditions(false);
        return parent::updateAll($this->model->tableName(), $attributes);
    }

    public function updateSql(array $attributes)
    {
        $this->prepareConditions(false);
        $command = $this->createCommand();
        $command->update($this->model->tableName(), $attributes, $this->where, $this->params);
        return $command->getRawSql();
    }

    public function updateCounters(array $counters)
    {
        /*
        $table = $this->model->tableName() . ' ' . $this->getTableAlias();
        return parent::updateCountersInternal($table, $this->makeAliasAttributes($counters));
        */
        $table = $this->model->tableName();
        return parent::updateCountersInternal($table, $counters);
    }

    public function getOrCreate(array $attributes)
    {
        $model = $this->filter($attributes)->get();
        $create = false;
        if ($model === null) {
            $model = $this->model;
            $model->setAttributes($attributes);
            $model->save();
            $create = true;
        }

        return [$model, $create];
    }

    public function updateOrCreate(array $attributes, array $updateAttributes)
    {
        /** @var Model $model */
        $model = $this->filter($attributes)->get();
        if (!$model) {
            $model = $this->model;
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
        return $this->limit($pageSize)->offset($page > 1 ? $pageSize * ($page - 1) : 0);
    }

    public function allSql()
    {
        $this->prepareConditions();
        $group = $this->groupBy;

        if ($this->_chainedHasMany) {
            if ($this->getDb()->getSchema() instanceof \Mindy\Query\Pgsql\Schema) {
                $pk = $this->quoteColumnName($this->retreivePrimaryKey());
                $this->distinct([
                    $this->_tableAlias . '.' . $pk => $this->_tableAlias . '.' . $pk
                ]);
                $orderBy = $this->orderBy;
                if ($orderBy) {
                    $this->orderBy = array_merge([$this->_tableAlias . '.' . $pk => SORT_ASC], $orderBy);
                }
                $this->from = '(' . parent::allSql() . ') ' . $this->quoteColumnName("_tmp");
                $this->select = '*';
                $this->groupBy = null;
                $this->where = [];

                if ($orderBy) {
                    $orderFields = [];
                    foreach ($orderBy as $field => $order) {
                        $tmp = explode('.', $field);
                        $name = str_replace('"', '', end($tmp));
                        $orderFields[$this->quoteColumnName("_tmp") . '.' . $this->quoteColumnName($name)] = $order;
                    }
                    $this->orderBy = $orderFields;
                }
                $this->distinct = null;
                $this->join = [];
                return parent::allSql();
            }
        }

//        if ($this->_chainedHasMany && !$group) {
//            $this->groupBy($this->quoteColumnName($this->tableAlias) . '.' . $this->quoteColumnName($this->retreivePrimaryKey()));
//        }
        $return = parent::allSql();
        $this->groupBy = $group;
        return $return;
    }

    protected function prepareConditions($aliased = true, $autoGroup = true)
    {
        if ($this->_filterComplete === false) {
            foreach ($this->_filterAnd as $query) {
                $this->buildCondition($query, 'andWhere', ['and'], $aliased, $autoGroup);
            }

            foreach ($this->_filterOr as $query) {
                $this->buildCondition($query, 'orWhere', ['and'], $aliased, $autoGroup);
            }

            foreach ($this->_filterExclude as $query) {
                $this->buildCondition($query, 'excludeWhere', ['and'], $aliased, $autoGroup);
            }

            foreach ($this->_filterOrExclude as $query) {
                $this->buildCondition($query, 'excludeOrWhere', ['and'], $aliased, $autoGroup);
            }

            $this->_filterComplete = true;
        }

        return $this;
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
        $this->prepareConditions();
        return parent::getSql();
    }

    public function getCacheKey()
    {
        return md5(
            serialize($this->_filterAnd) .
            serialize($this->_filterOr) .
            serialize($this->_filterExclude) .
            serialize($this->_filterOrExclude)
        );
    }

    /**
     * Executes query and returns a single row of result.
     * @param array $filter
     * @return Orm|null
     * @throws MultipleObjectsReturned
     */
    public function get($filter = [])
    {
//        $cacheKey = $this->modelClass . '_' . $this->getCacheKey();
//        if ($this->asArray) {
//            $cacheKey .= '_array';
//        }
//        if (self::getCache()->exists($cacheKey)) {
//            return self::getCache()->get($cacheKey);
//        }

        if (!empty($filter)) {
            $this->filter($filter);
        }
        $this->prepareConditions();
        $rows = $this->createCommand()->queryAll();
        if (count($rows) > 1) {
            throw new MultipleObjectsReturned();
        } elseif (count($rows) === 0) {
            return null;
        }

        $rows = !empty($this->with) ? $this->populateWith($rows) : $rows;
        $row = array_shift($rows);
        $result = $this->asArray ? $row : $this->createModel($row);

        // self::getCache()->set($cacheKey, $result);

        $this->_filterComplete = false;
        return $result;
    }

    /**
     * Creates a DB command that can be used to execute this query.
     * @return \Mindy\Query\Command the created DB command instance.
     */
    public function createCommand()
    {
        /** @var Orm $modelClass */
        $modelClass = $this->modelClass;
        $db = $this->getDb();

        $select = $this->select;
        $from = $this->from;

        if ($this->from === null) {
            $tableName = $modelClass::tableName();
            if (empty($this->select) && !empty($this->join)) {
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
     * @param array|string $keyChain
     * @param string $alias
     * @param object $model
     */
    protected function addChain($keyChain, $alias, $model)
    {
        if (is_array($keyChain)) {
            $keyChain = $this->prefixToKey($keyChain);
        }

        $this->_chains[$keyChain] = [
            'alias' => $alias,
            'model' => $model
        ];
    }

    /**
     * Makes alias for joined table
     * @param $table
     * @param bool $increment
     * @return string
     */
    public function makeAliasKey($table, $increment = true)
    {
        if ($increment) {
            $this->_aliasesCount += 1;
        }
        $schema = $this->getDb()->getSchema();
        $table = $schema->getRawTableName($table);
        return $schema->quoteTableName($table . '_' . $this->_aliasesCount);
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

    /**
     * Makes connection by chain (creates joins)
     * @param $prefix
     */
    protected function makeChain(array $prefix, $prefixedSelect = false, $autoGroup = true)
    {
        // Searching closest already connected relation
        list($model, $alias, $prefix, $chain) = $this->searchChain($prefix);

        foreach ($prefix as $relationName) {
            $through = false;
            $throughChain = null;
            $throughAlias = null;
            $throughModel = null;

            if (substr($relationName, -8) == '_through') {
                $throughChain = array_merge($chain, [$relationName]);
                $through = true;
                $relationName = substr($relationName, 0, strlen($relationName) - 8);
            }

            $chain[] = $relationName;
            /** @var Model $model */
            /** @var \Mindy\Orm\Fields\RelatedField $relatedValue */
            $relatedValue = $model->getField($relationName);

            // TODO prefetch_related
            if ($prefixedSelect && $relatedValue instanceof ManyToManyField) {
                continue;
            }

            if ($relatedValue instanceof ManyToManyField) {
                list($throughModelInfo, $manyModelInfo) = $relatedValue->processQuerySet($this, $alias, $autoGroup);
                if ($through) {
                    if (!$throughModelInfo) {
                        throw new Exception("Through model is not specified");
                    }
                    list($throughModel, $throughAlias) = $throughModelInfo;
                }
                list($relatedModel, $newAlias) = $manyModelInfo;
            } else {
                list($relatedModel, $newAlias) = $relatedValue->processQuerySet($this, $alias, $autoGroup);
            }
            $alias = $newAlias;

            if ($prefixedSelect) {
                $selectNames = [];
                $selectRelatedNames = [];
                /** @var \Mindy\Orm\Model $relatedModel */
                $columnNames = $relatedModel->getTableSchema()->getColumnNames();
                foreach ($columnNames as $item) {
                    $selectRelatedNames[] = $alias . '.' . $this->quoteColumnName($item) . ' AS ' . $relationName . '__' . $item;
                }
                $oldSelect = $this->select;
                $this->select(array_merge($selectNames, $selectRelatedNames));
                $this->select = array_merge($this->select, $oldSelect);
            }

            $this->addChain($chain, $alias, $relatedModel);
            $model = $relatedModel;

            if ($through) {
                $this->addChain($throughChain, $throughAlias, $throughModel);
                $model = $throughModel;
            }
        }
    }

    /**
     * Returns chain if exists
     * @param array|string $keyChain
     * @return null|array
     */
    protected function getChain($keyChain)
    {
        if (is_array($keyChain)) {
            $keyChain = $this->prefixToKey($keyChain);
        }

        if (isset($this->_chains[$keyChain])) {
            return $this->_chains[$keyChain];
        }

        return null;
    }

    public function with(array $value)
    {
        foreach ($value as $name) {
            if ($this->model->getMeta()->hasRelatedField($name)) {
                $this->with[] = $name;
                $this->getOrCreateChainAlias([$name], true);
            }
        }
        return $this;
    }

    /**
     * Returns chain alias
     * @param array|string $keyChain
     * @return string
     */
    protected function getChainAlias($keyChain)
    {
        $chain = $this->getChain($keyChain);
        return $chain ? $chain['alias'] : '';
    }

    /**
     * Get or create alias and related model by chain
     * @param array $prefix
     * @param bool $prefixedSelect
     * @return array
     */
    protected function getOrCreateChainAlias(array $prefix, $prefixedSelect = false, $autoGroup = true)
    {
        if (!$this->from) {
            $this->from($this->model->tableName() . ' ' . $this->tableAlias);
            if (empty($this->select)) {
                $this->select($this->tableAlias . '.*');
            }
        }

        if (count($prefix) > 0) {
            $chain = $this->getChain($prefix);
            if ($chain === null) {
                $this->makeChain($prefix, $prefixedSelect, $autoGroup);
                $chain = $this->getChain($prefix);
            }

            if ($chain) {
                return [
                    $chain['alias'],
                    $chain['model']
                ];
            }
        }

        return [
            $this->tableAlias,
            $this->model
        ];
    }

    /**
     * Example:
     * >>> $user = User::objects()->get(['pk' => 1]);
     * >>> $pages = Page::object()->filter(['user__in' => [$user]])->all();
     * @param array $query
     * @param bool $aliased
     * @throws \Mindy\Exception\Exception
     * @return array
     */
    protected function parseLookup(array $query, $aliased = true, $autoGroup = true)
    {
        $queryBuilder = $this->getQueryBuilder();
        $lookupQuery = [];
        $lookupParams = [];

        $resultQuery = [];
        foreach ($query as $key => $queryItem) {
            if ($queryItem instanceof Q) {
                $queryCondition = $queryItem->getQueryCondition();
                $expressionQueryJoin = $queryItem->getQueryJoinCondition();
                $expressionConditionGroups = $queryItem->getConditions();

                $expressionParams = [];
                $expressionQuery = [];

                foreach ($expressionConditionGroups as $expressionConditions) {
                    list($conditionQuery, $conditionParams) = $this->parseLookup($expressionConditions, $aliased, $autoGroup);

                    $expressionQuery[] = array_merge($expressionQueryJoin, $conditionQuery);
                    $expressionParams = array_merge($expressionParams, $conditionParams);
                }

                $lookupParams = array_merge($lookupParams, $expressionParams);
                $lookupQuery[] = array_merge($queryCondition, $expressionQuery);
            } else {
                $resultQuery[$key] = $queryItem;
            }
        }


        $query = $resultQuery;
        $lookup = new LookupBuilder($query);

        foreach ($lookup->parse($queryBuilder) as $data) {
            list($prefix, $field, $condition, $params) = $data;

            // Issue #124 https://github.com/studio107/Mindy_Orm/issues/124
            $fkList = array_flip($this->model->getMeta()->getForeignFields());
            if ((($params instanceof Base) == false) && array_key_exists($field, $fkList)) {
                $field .= '_id';
            }

            /** @var Model $model */
            list($alias, $model) = $this->getOrCreateChainAlias($prefix, false, $autoGroup);

            if ($field === 'pk') {
                $field = $model->getPkName();
            }

            if (is_object($params) && ($params instanceof QuerySet || $params instanceof Manager)) {
                if ($condition != 'in') {
                    throw new Exception("QuerySet object can be used as a parameter only in case of 'in' condition");
                } else {
                    if ($params instanceof Manager) {
                        $params = $params->getQuerySet();
                    }
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

            if ($aliased === true || is_string($aliased)) {
                if (strpos($field, '.') === false) {
                    if (is_string($aliased)) {
                        $field = $aliased . '.' . $field;
                    } else if ($alias) {
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
            $lookupParams = array_merge($lookupParams, $params);
            $lookupQuery[] = $query;
        }

        return [$lookupQuery, $lookupParams];
    }

    /**
     * @param array $query
     * @param $method
     * @param array $queryCondition
     * @param bool $aliased
     * @return $this
     */
    public function buildCondition(array $query, $method, $queryCondition = [], $aliased = true, $autoGroup = true)
    {
        list($condition, $params) = $this->parseLookup($query, $aliased, $autoGroup);
        $this->$method(array_merge($queryCondition, $condition), $params);
        return $this;
    }

    public function clearFilter()
    {
        $this->command = null;
        $this->_filterComplete = false;
        $this->_filterAnd = [];
        $this->_filterExclude = [];
        $this->_filterOr = [];
        $this->_filterOrExclude = [];
        $this->where = [];
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
    }

    /**
     * @param array $query
     * @return $this
     */
    public function orExclude(array $query)
    {
        $this->_filterOrExclude[] = $query;
        return $this;
    }

    /**
     * @param $condition
     * @param array $params
     * @return static
     */
    public function excludeWhere($condition, $params = [])
    {
        return parent::andWhere(['not', $condition], $params);
    }

    /**
     * @param $condition
     * @param array $params
     * @return static
     */
    public function excludeOrWhere($condition, $params = [])
    {
        return parent::orWhere(['not', $condition], $params);
    }

    /**
     * Converts name => `name`, user.name => `user`.`name`
     * @param string $name Column name
     * @return string Quoted column name
     */
    public function quoteColumnName($name)
    {
        return $this->getDb()->quoteColumnName($name);
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
            $column = $column == '?' ? $this->getQueryBuilder()->getRandomOrder() : $this->aliasColumn($column);
            $result[$column] = $sort;
        }
        return $result;
    }

    /**
     * Order by alias
     * @param $columns
     * @return static
     */
    public function order(array $columns)
    {
        $orderBy = [];
        $meta = $this->model->getMeta();
        foreach ($columns as $column) {
            $isReverse = strpos($column, '-') === 0;
            $t = str_replace('-', '', $column);
            if ($t === 'pk') {
                $column = $this->model->getPkName();
                if ($isReverse) {
                    $column = '-' . $column;
                }
            } else if ($meta->hasForeignField($t)) {
                if ($meta->hasField($t)) {
                    $column .= "_id";
                }
            }
            $orderBy[] = $column;
        }

        $this->orderBy($orderBy);

        if ($this->getDb()->getSchema() instanceof \Mindy\Query\Pgsql\Schema) {

            $orderFields = array_keys($this->orderBy);
            $this->select = array_merge($this->select, $orderFields);
            $tableSchema = $this->getDb()->getSchema()->getTableSchema($this->model->tableName());
            if ($tableSchema === null) {
                throw new Exception("Table " . $this->model->tableName() . " missing in database");
            }
            $groupFields = [];
            foreach ($tableSchema->getColumnNames() as $name) {
                $groupFields[] = $this->_tableAlias . '.' . $this->quoteColumnName($name);
            }
            $groupBy = array_merge($orderFields, $groupFields);
            foreach ($this->_chains as $name => $chain) {
                $groupBy[] = $chain['alias'] . '.' . $chain['model']->getPkName();
            }
            if ($this->groupBy) {
                $this->groupBy = array_merge($this->groupBy, $groupBy);
            } else {
                $this->groupBy = $groupBy;
            }
        }

        return $this;
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

        // Issue #124 https://github.com/studio107/Mindy_Orm/issues/124
        $fkList = array_flip($this->model->getMeta()->getForeignFields());
        if ((($params instanceof Base) == false) && array_key_exists($field, $fkList)) {
            $field .= '_id';
        }

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
     * @param null|string $alias
     * @return array new attributes with table aliases
     */
    protected function makeAliasAttributes(array $attributes, $alias = null)
    {
        $alias = $alias ? $alias : $this->getTableAlias();
        $new = [];
        foreach ($attributes as $key => $value) {
            $new[$alias . '.' . $key] = $value;
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
        return strpos($value, '.') !== false ? floatval($value) : intval($value);
    }

    /**
     * @param string $column
     * @return float|int
     */
    public function sum($column)
    {
        $this->prepareConditions(true, false);
        if ($this->groupBy && $this->getSchema() instanceof \Mindy\Query\Pgsql\Schema) {
            $value = parent::sum('c.' . $column);
        } else {
            $value = parent::sum($this->aliasColumn($column));
        }
        return $this->numval($value);
    }

    /**
     * @param string $column
     * @return float|int
     */
    public function sumSql($column)
    {
        $this->prepareConditions(true, false);
        return parent::sumSql($this->aliasColumn($column));
    }

    /**
     * @param string $column
     * @return float|int
     */
    public function average($column)
    {
        $this->prepareConditions(true, false);
        if ($this->groupBy && $this->getSchema() instanceof \Mindy\Query\Pgsql\Schema) {
            $value = parent::average('c.' . $column);
        } else {
            $value = parent::average($this->aliasColumn($column));
        }
        return $this->numval($value);
    }

    /**
     * @param string $column
     * @return float|int
     */
    public function averageSql($column)
    {
        $this->prepareConditions(true, false);
        return parent::averageSql($this->aliasColumn($column));
    }

    /**
     * @param string $column
     * @return float|int
     */
    public function min($column)
    {
        $this->prepareConditions(true, false);
        if ($this->groupBy && $this->getSchema() instanceof \Mindy\Query\Pgsql\Schema) {
            $value = parent::min('c.' . $column);
        } else {
            $value = parent::min($this->aliasColumn($column));
        }
        return $this->numval($value);
    }

    /**
     * @param string $column
     * @return float|int
     */
    public function minSql($column)
    {
        $this->prepareConditions(true, false);
        return parent::minSql($this->aliasColumn($column));
    }

    /**
     * @param string $column
     * @return float|int
     */
    public function max($column)
    {
        $this->prepareConditions(true, false);
        if ($this->groupBy && $this->getSchema() instanceof \Mindy\Query\Pgsql\Schema) {
            $value = parent::max('c.' . $column);
        } else {
            $value = parent::max($this->aliasColumn($column));
        }
        return $this->numval($value);
    }

    /**
     * @param string $column
     * @return float|int
     */
    public function maxSql($column)
    {
        $this->prepareConditions(true, false);
        return parent::maxSql($this->quoteColumnName($column));
    }

    private function filterHasJoin()
    {
        $meta = $this->model->getMeta();
        foreach ([$this->_filterAnd, $this->_filterOr, $this->_filterExclude, $this->_filterOrExclude] as $data) {
            foreach ($data as $subFilter) {
                foreach ($subFilter as $key => $value) {
                    if (strpos($key, '__') !== false) {
                        $parts = explode('__', $key);
                        if ($meta->hasRelatedField(array_shift($parts))) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    protected function prepareDelete()
    {
        $tableName = $this->model->tableName();

        if ($this->filterHasJoin()) {
            $this->prepareConditions();
            return $this->createCommand()->delete($tableName, [
                $this->retreivePrimaryKey() => $this->valuesList(['pk'], true)
            ], $this->params);
        } else {
            $this->prepareConditions(false);
            return $this->createCommand()->delete($tableName, $this->where, $this->params);
        }
    }

    public function delete()
    {
        return $this->prepareDelete()->execute();
    }

    public function deleteSql()
    {
        return $this->prepareDelete()->getRawSql();
    }

    /**
     * @param $command \Mindy\Query\Command
     * @return $this
     */
    protected function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return array|Model[]
     */
    public function getData()
    {
        if (empty($this->_data)) {
            if (
                $this->command === null &&
                $this->_chainedHasMany &&
                $this->getDb()->getSchema() instanceof \Mindy\Query\Pgsql\Schema
            ) {
                $pk = $this->quoteColumnName($this->retreivePrimaryKey());
                $this->distinct([
                    $this->_tableAlias . '.' . $pk => $this->_tableAlias . '.' . $pk
                ]);
                $orderBy = $this->orderBy;
                if ($orderBy) {
                    $this->orderBy = array_merge([$this->_tableAlias . '.' . $pk => SORT_ASC], $orderBy);
                }
                $this->from = '(' . parent::allSql() . ') ' . $this->quoteColumnName("_tmp");
                $this->select = '*';
                $this->groupBy = false;
                $this->where = [];

                if ($orderBy) {
                    $orderFields = [];
                    foreach ($orderBy as $field => $order) {
                        $tmp = explode('.', $field);
                        $name = str_replace('"', '', end($tmp));
                        $orderFields[$this->quoteColumnName("_tmp") . '.' . $this->quoteColumnName($name)] = $order;
                    }
                    $this->orderBy = $orderFields;
                }
                $this->distinct = null;
                $this->join = [];
            }

            $this->prepareCommand();
            $data = $this->command->queryAll();
            $this->_data = !empty($this->with) ? $this->populateWith($data) : $data;
            $this->with = [];
            $this->command = null;
            $this->_filterComplete = true;
        }
        return $this->asArray ? $this->_data : $this->createModels($this->_data);
    }

    /**
     * @param null|string $q
     * @return string
     */
    public function countSql($q = '*')
    {
        $this->prepareConditions();
        if ($this->_chainedHasMany && $this->distinct !== false) {
            $this->distinct();
            $q = $this->quoteColumnName($this->tableAlias) . '.' . $this->quoteColumnName($this->retreivePrimaryKey());
        }
        return parent::countSql($q);
    }

    /**
     * @param string $q
     * @return int
     */
    public function count($q = '*')
    {
        if (!empty($this->_data)) {
            return count($this->_data);
        } else {
            $this->prepareConditions();

            $column = $this->quoteColumnName($this->retreivePrimaryKey());
            if ($this->_chainedHasMany) {
                if ($this->groupBy) {
                    // TODO Если раскоментировать строку, getIterator, getData работают не корректно. С ней и без нее тесты проходят.
                    // $this->select([$column => $this->aliasColumn($column)]);
                    $value = parent::count($this->quoteColumnName($column));
                } else {
                    $value = parent::count($this->aliasColumn($column));
                }
            } else {
                $value = parent::count($q);
            }
            return $value;
        }
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

    public function addGroupBy($columns)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        $newColumns = [];
        foreach ($columns as $column) {
            if ($column == 'pk') {
                $column = $this->model->getPkName();
            }
            $newColumns[] = $this->quoteColumnName($this->tableAlias . '.' . $column);
        }
        return parent::addGroupBy($newColumns);
    }

    /**
     * Truncate table
     * @return int
     * @throws \Mindy\Query\Exception
     */
    public function truncate()
    {
        return $this->createCommand()->truncateTable($this->model->tableName())->execute();
    }

    /**
     * @param mixed $fields
     * @return $this
     */
    public function distinct($fields = true)
    {
        parent::distinct($fields);
        return $this;
    }

    /**
     * @param $fields
     * @return $this
     */
    public function group($fields)
    {
        if (is_string($fields)) {
            if (strpos($fields, '.') !== false) {
                $this->groupBy[] = $this->quoteColumnName($fields);
            } else {
                if ($fields == 'pk') {
                    $fields = $this->retreivePrimaryKey();
                }
                $this->groupBy[] = $this->_tableAlias . '.' . $this->quoteColumnName($fields);
            }
        } else if (is_array($fields)) {
            foreach ($fields as $field) {
                if (strpos($field, '.') !== false) {
                    $this->groupBy[] = $this->quoteColumnName($field);
                } else {
                    if ($fields == 'pk') {
                        $fields = $this->retreivePrimaryKey();
                    }
                    $this->groupBy[] = $this->_tableAlias . '.' . $this->quoteColumnName($field);
                }
            }
        }
        $this->groupBy = array_unique($this->groupBy);
        return $this;
    }

    public function setChainedHasMany()
    {
        $this->_chainedHasMany = true;
        return $this;
    }
}
