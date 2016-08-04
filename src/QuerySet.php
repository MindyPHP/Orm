<?php

namespace Mindy\Orm;

use Mindy\Exception\Exception;
use Mindy\Helper\Creator;
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
     * Executes query and returns all results as an array.
     * If null, the DB connection returned by [[modelClass]] will be used.
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all()
    {
        $rows = $this->getDb()->createCommand($this->allSql())->queryAll();
        return $this->asArray ? $rows : $this->createModels($rows);
    }

    /**
     * @param int $batchSize
     * @return \Mindy\Orm\BatchDataIterator
     */
    public function batch($batchSize = 100)
    {
        return new BatchDataIterator([
            'qs' => $this,
            'batchSize' => $batchSize,
            'db' => $this->getDb(),
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
        return new BatchDataIterator([
            'qs' => $this,
            'batchSize' => $batchSize,
            'db' => $this->getDb(),
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
        $rows = $this->createCommand($qb->select($columns)->toSQL())->queryAll();

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
        return $this->createCommand($this->updateSql($attributes))->execute();
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function updateSql(array $attributes)
    {
        return $this->getQueryBuilder()->setTypeUpdate()->update($this->model->tableName(), $attributes)->toSQL();
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
        $this->getQueryBuilder()->paginate($page, $pageSize);
        return $this;
    }

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
     * @return Orm|null
     * @throws MultipleObjectsReturned
     */
    public function get($filter = [])
    {
        $rows = $this->createCommand($this->getSql($filter))->queryAll();
        if (count($rows) > 1) {
            throw new MultipleObjectsReturned();
        } elseif (count($rows) === 0) {
            return null;
        }

        if (!empty($this->with)) {
            $rows = $this->populateWith($rows);
        }
        $row = array_shift($rows);
        return $this->asArray ? $row : $this->createModel($row);
    }

    /**
     * @return mixed|null
     */
    public function retreivePrimaryKey()
    {
        return $this->getModel()->primaryKeyName();
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
    protected function makeChain(array $prefix, $prefixedSelect = false, $autoGroup = true, array $select = [])
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
                if (!empty($select)) {
                    $columnNames = $select;
                } else {
                    $columnNames = $relatedModel->getMeta()->getAttributes();
                }
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
    protected function getOrCreateChainAlias(array $prefix, $prefixedSelect = false, $autoGroup = true, array $select = [])
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
                $this->makeChain($prefix, $prefixedSelect, $autoGroup, $select);
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
            $meta = $this->model->getMeta();
            $fkList = array_flip(array_merge($meta->getForeignFields(), $meta->getOneToOneFields()));
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
                    if (is_a($initField, ForeignField::class)) {
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
     * @param null|string|array $q
     * @return float|int
     */
    public function sum($q)
    {
        return $this->getDb()->createCommand($this->sumSql($q))->queryScalar();
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
        $value = $this->getDb()->createCommand($this->buildAggregateSql($q))->queryScalar();
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

    /**
     * @return bool
     */
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

    /**
     * @return int
     * @throws \Mindy\Query\Exception
     */
    public function delete()
    {
        return $this->createCommand($this->deleteSql())->execute();
    }

    public function deleteSql()
    {
//        if ($this->filterHasJoin()) {
//            $this->prepareConditions();
//            return $this->createCommand()->delete($tableName, [
//                $this->retreivePrimaryKey() => $this->valuesList(['pk'], true)
//            ], $this->params);
//        }

        $builder = $this->getQueryBuilder()
            ->setTypeDelete()
            ->setAlias(null);
        return $builder->toSQL();
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
     * @param $columns
     * @return $this
     */
    public function group($columns)
    {
        $this->getQueryBuilder()->group($columns);
        return $this;
    }

    public function setChainedHasMany()
    {
        $this->_chainedHasMany = true;
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
