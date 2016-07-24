<?php

namespace Mindy\Orm\Fields;

use Mindy\Orm\QuerySet;
use Mindy\Query\Connection;
use Mindy\Query\ConnectionManager;
use Mindy\QueryBuilder\QueryBuilder;

/**
 * Class RelatedField
 * @package Mindy\Orm
 */
abstract class RelatedField extends IntField
{
    /**
     * @var string
     */
    public $relatedName;
    /**
     * @var string
     */
    public $modelClass;

    protected $_model;

    protected $_relatedModel;
    /**
     * @var Connection
     */
    private $_db;

    public function getRelatedName()
    {
        if (!$this->relatedName) {
            $this->relatedName = $this->name . '_set';
        }
        return $this->relatedName;
    }

    abstract public function getJoin(QueryBuilder $qb, $topAlias);

    abstract protected function fetch($value);

    /**
     * @return \Mindy\Orm\Model
     */
    public function getRelatedModel()
    {
        if (!$this->_relatedModel) {
            $this->_relatedModel = new $this->modelClass();
        }
        return $this->_relatedModel;
    }

    public function getTable()
    {
        $cls = $this->ownerClassName;
        return $cls::tableName();
    }

    /**
     * @return Connection
     */
    protected function getDb()
    {
        return $this->_db;
    }

    /**
     * @param Connection $db
     * @return $this
     */
    public function setDb(Connection $db)
    {
        $this->_db = $db;
        return $this;
    }

    public function getRelatedTable()
    {
        $cls = $this->modelClass;
        return $cls::tableName();
    }

    public function buildQuery(QueryBuilder $qb, $topAlias)
    {
        $alias = '?';
        foreach ($this->getJoin($qb, $topAlias) as $join) {
            list($joinType, $tableName, $on, $alias) = $join;
            $qb->join($joinType, $tableName, $on, $alias);
        }
        return $alias;
    }
}
