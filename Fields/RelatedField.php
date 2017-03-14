<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Connection;
use Mindy\QueryBuilder\QueryBuilder;

/**
 * Class RelatedField.
 */
abstract class RelatedField extends IntField
{
    /**
     * @var string
     */
    public $modelClass;

    protected $_model;

    protected $_relatedModel;
    /**
     * @var Connection
     */
    protected $connection;

    abstract public function getJoin(QueryBuilder $qb, $topAlias);

    abstract public function getSelectJoin(QueryBuilder $qb, $topAlias);

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
        return call_user_func([$this->ownerClassName, 'tableName']);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param Connection $db
     *
     * @return $this
     */
    public function setConnection(Connection $db)
    {
        $this->connection = $db;

        return $this;
    }

    public function getRelatedTable()
    {
        return call_user_func([$this->modelClass, 'tableName']);
    }

    public function buildSelectQuery(QueryBuilder $qb, $topAlias)
    {
        $joinAlias = '???';
        foreach ($this->getSelectJoin($qb, $topAlias) as $join) {
            list($joinType, $tableName, $on, $alias) = $join;
            if ($qb->hasJoin($tableName)) {
                $joinAlias = $qb->getJoinAlias($tableName);
            } else {
                $qb->join($joinType, $tableName, $on, $alias);
                $joinAlias = $alias;
            }
        }

        return $joinAlias;
    }

    public function buildQuery(QueryBuilder $qb, $topAlias)
    {
        $joinAlias = '???';
        foreach ($this->getJoin($qb, $topAlias) as $join) {
            list($joinType, $tableName, $on, $alias) = $join;
            if ($qb->hasJoin($tableName)) {
                $joinAlias = $qb->getJoinAlias($tableName);
            } else {
                $qb->join($joinType, $tableName, $on, $alias);
                $joinAlias = $alias;
            }
        }

        return $joinAlias;
    }

    abstract public function getManager();
}
