<?php

namespace Mindy\Orm;

use Mindy\Exception\Exception;
use Mindy\Orm\Exception\MultipleObjectsReturned;
use Mindy\Orm\Fields\ManyToManyField;

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
        $group = $this->groupBy;
        if ($this->_chainedHasMany && !$group) {
            $this->groupBy($this->quoteColumnName($this->tableAlias . '.' . $this->retreivePrimaryKey()));
        }
        $command = $this->createCommand();
        $this->groupBy = $group;
        $this->setCommand($command);
        return $this;
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
        if ($this->_chainedHasMany && !$group) {
            $this->groupBy($this->quoteColumnName($this->tableAlias . '.' . $this->retreivePrimaryKey()));
        }

        $valuesSelect = [];
        foreach ($fieldsList as $name) {
            if ($name == 'pk') {
                $name = $this->model->getPkName();
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
        $table = $this->model->tableName() . ' ' . $this->getTableAlias();
        return parent::updateCountersInternal($table, $this->makeAliasAttributes($counters));
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
        if ($this->_chainedHasMany && !$group) {
            $this->groupBy($this->quoteColumnName($this->tableAlias . '.' . $this->retreivePrimaryKey()));
        }
        $return = parent::allSql();
        $this->groupBy = $group;
        return $return;
    }

    protected function prepareConditions($aliased = true)
    {
        if ($this->_filterComplete === false) {
            foreach ($this->_filterAnd as $query) {
                $this->buildCondition($query, 'andWhere', ['and'], $aliased);
            }

            foreach ($this->_filterOr as $query) {
                $this->buildCondition($query, 'orWhere', ['and'], $aliased);
            }

            foreach ($this->_filterExclude as $query) {
                $this->buildCondition($query, 'excludeWhere', ['and'], $aliased);
            }

            foreach ($this->_filterOrExclude as $query) {
                $this->buildCondition($query, 'excludeOrWhere', ['and'], $aliased);
            }

            $this->_filterComplete = true;
        }

        if (!empty($this->with)) {
            foreach ($this->with as $name) {
                $this->getOrCreateChainAlias([$name], true);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSql()
    {
        $this->prepareConditions();
        return parent::getSql();
    }

    public function getCacheKey()
    {
        return md5(serialize($this->_filterAnd) .
            serialize($this->_filterOr) .
            serialize($this->_filterExclude) .
            serialize($this->_filterOrExclude));
    }

    /**
     * Executes query and returns a single row of result.
     * @throws \Mindy\Orm\Exception\MultipleObjectsReturned
     * @return null|Orm
     */
    public function get()
    {
//        $cacheKey = $this->modelClass . '_' . $this->getCacheKey();
//        if ($this->asArray) {
//            $cacheKey .= '_array';
//        }
//        if (self::getCache()->exists($cacheKey)) {
//            return self::getCache()->get($cacheKey);
//        }

        $this->prepareConditions();
        $rows = $this->createCommand()->queryAll();
        if (count($rows) > 1) {
            throw new MultipleObjectsReturned();
        } elseif (count($rows) === 0) {
            return null;
        }
        $row = array_shift($rows);
        $result = $this->asArray ? $row : $this->createModel($row);

        // self::getCache()->set($cacheKey, $result);

        $this->_filterComplete = false;
        return $result;
    }

    /**
     * @param null|string $q
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

        $this->_chains[$keyChain] = ['alias' => $alias, 'model' => $model];
    }

    /**
     * Makes alias for joined table
     * @param $table
     * @param bool $increment
     * @return string
     */
    protected function makeAliasKey($table, $increment = true)
    {
        if ($increment) {
            $this->_aliasesCount += 1;
        }
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
    protected function makeChain(array $prefix, $prefixedSelect = false)
    {
        // Searching closest already connected relation
        list($model, $alias, $prefix, $chain) = $this->searchChain($prefix);

        foreach ($prefix as $relationName) {
            $chain[] = $relationName;
            /** @var Model $model */
            $relatedValue = $model->getField($relationName);

            // TODO prefetch_related
            if ($prefixedSelect && $relatedValue instanceof ManyToManyField) {
                continue;
            }
            list($relatedModel, $joinTables) = $relatedValue->getJoin();

            foreach ($joinTables as $join) {
                $type = isset($join['type']) ? $join['type'] : 'LEFT JOIN';
                $newAlias = $this->makeAliasKey($join['table']);
                $table = $join['table'] . ' ' . $newAlias;

                $from = $alias . '.' . $join['from'];
                $to = $newAlias . '.' . $join['to'];
                $on = $this->quoteColumnName($from) . ' = ' . $this->quoteColumnName($to);

                $this->join($type, $table, $on);

                // Has many relations (we must work only with current model lines - exclude duplicates)
                if (isset($join['group']) && ($join['group']) && !$this->_chainedHasMany) {
                    $this->_chainedHasMany = true;
                }

                $alias = $newAlias;
            }

            if ($prefixedSelect) {
                $selectNames = [];
                $selectRelatedNames = [];
                foreach (array_keys($relatedModel->getTableSchema()->columns) as $item) {
                    $selectRelatedNames[] = $alias . '.' . $this->quoteColumnName($item) . ' AS ' . strtolower($relatedModel->classNameShort()) . '__' . $item;
                }
                $oldSelect = $this->select;
                $this->select(array_merge($selectNames, $selectRelatedNames));
                $this->select = array_merge($this->select, $oldSelect);
            }

            $this->addChain($chain, $alias, $relatedModel);

            $model = $relatedModel;
        }
    }

    /**
     * Returns chain if exists
     * @param array|string $keyChain
     * @return null|array
     */
    protected function getChain($keyChain)
    {
        if (is_array($keyChain))
            $keyChain = $this->prefixToKey($keyChain);

        if (isset($this->_chains[$keyChain])) {
            return $this->_chains[$keyChain];
        }
        return null;
    }

    public function with(array $value)
    {
        $fetch = [];
        foreach ($value as $name) {
            if ($this->model->getMeta()->hasRelatedField($name)) {
                $fetch[] = $name;
            }
        }
        $this->with = $fetch;
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
    protected function getOrCreateChainAlias(array $prefix, $prefixedSelect = false)
    {
        if (!$this->from) {
            $this->from($this->model->tableName() . ' ' . $this->tableAlias);
            if (empty($this->select)) {
                $this->select($this->tableAlias . '.*');
            }
        }

        if (count($prefix) > 0) {
            if (!($chain = $this->getChain($prefix))) {
                $this->makeChain($prefix, $prefixedSelect);
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
     * @param bool $aliased
     * @throws \Mindy\Exception\Exception
     * @return array
     */
    protected function parseLookup(array $query, $aliased = true)
    {
        $queryBuilder = $this->getQueryBuilder();

        $lookup = new LookupBuilder($query);
        $lookupQuery = [];
        $lookupParams = [];

        foreach ($lookup->parse() as $data) {
            list($prefix, $field, $condition, $params) = $data;
            /** @var Model $model */
            list($alias, $model) = $this->getOrCreateChainAlias($prefix);

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
            if ($t == 'pk') {
                $column = $this->model->getPkName();
                if ($isReverse) {
                    $column = '-' . $column;
                }
            }
            if ($meta->hasForeignField($t)) {
                if ($meta->hasField($t)) {
                    $column .= "_id";
                }
            }
            $orderBy[] = $column;
        }

        return $this->orderBy($orderBy);
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
        if (strpos($value, '.') !== false) {
            return floatval($value);
        } else {
            return intval($value);
        }
    }

    /**
     * @param string $column
     * @return float|int
     */
    public function sum($column)
    {
        $this->prepareConditions();
        $column = $this->aliasColumn($column);
        $value = parent::sum($column);
        return $this->numval($value);
    }

    /**
     * @param string $column
     * @return float|int
     */
    public function average($column)
    {
        $this->prepareConditions();
        $column = $this->aliasColumn($column);
        $value = parent::average($column);
        return $this->numval($value);
    }

    /**
     * @param string $column
     * @return float|int
     */
    public function min($column)
    {
        $this->prepareConditions();
        $column = $this->aliasColumn($column);
        $value = parent::min($column);
        return $this->numval($value);
    }

    /**
     * @param string $column
     * @return float|int
     */
    public function max($column)
    {
        $this->prepareConditions();
        $column = $this->aliasColumn($column);
        $value = parent::max($column);
        return $this->numval($value);
    }

    public function delete()
    {
        $this->prepareConditions(false);
//        $alias = $this->getTableAlias();
//        $tableName = $alias . " USING " . $this->model->tableName() . " AS " . $alias;
        $tableName = $this->model->tableName();
        return $this->createCommand()->delete($tableName, $this->where, $this->params)->execute();
    }

    public function deleteSql()
    {
        $this->prepareConditions(false);
        $tableName = $this->model->tableName();
        return $this->createCommand()->delete($tableName, $this->where, $this->params)->getRawSql();
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
            if ($this->command === null) {
                $this->prepareCommand();
            }
            $data = $this->command->queryAll();
            $this->_data = !empty($this->with) ? $this->populateWith($data) : $data;
            $this->with = [];
            $this->command = null;
            $this->_filterComplete = false;
        }
        return $this->asArray ? $this->_data : $this->createModels($this->_data);
    }

    /**
     * @param string $q
     * @return int
     */
    public function count($q = '*')
    {
        if (!empty($this->_data)) {
            $count = count($this->_data);
        } else {
            $this->prepareConditions();
            if ($this->_chainedHasMany) {
                $q = 'DISTINCT ' . $this->quoteColumnName($this->tableAlias . '.' . $this->retreivePrimaryKey());
            } else {
                $q = '*';
            }
            $count = parent::count($q);
        }
        return $count;
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
}
