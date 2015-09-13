<?php

namespace Mindy\Orm\Fields;

use Mindy\Exception\Exception;
use Mindy\Orm\QuerySet;
use Mindy\Query\ConnectionManager;

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

    public function getRelatedName()
    {
        if (!$this->relatedName) {
            $this->relatedName = $this->name . '_set';
        }
        return $this->relatedName;
    }

    abstract public function getJoin();

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

    public function getTable($clean = true)
    {
        $ownerClassName = $this->ownerClassName;
        $tableName = $ownerClassName::tableName();
        $schema = ConnectionManager::getDb()->getSchema();
        return $clean ? $schema->getRawTableName($tableName) : $tableName;
    }

    public function getRelatedTable($clean = true)
    {
        $tableName = $this->getRelatedModel()->tableName();
        $schema = ConnectionManager::getDb()->getSchema();
        return $clean ? $schema->getRawTableName($tableName) : $tableName;
    }

    public function processQuerySet(QuerySet $qs, $alias, $autoGroup = true)
    {
        list($relatedModel, $joinTables) = $this->getJoin();
        foreach ($joinTables as $join) {
            $type = isset($join['type']) ? $join['type'] : 'LEFT OUTER JOIN';
            $newAlias = $qs->makeAliasKey($join['table']);
            $table = $join['table'] . ' ' . $newAlias;

            $from = $alias . '.' . $join['from'];
            $to = $newAlias . '.' . $join['to'];
            $on = $qs->quoteColumnName($from) . ' = ' . $qs->quoteColumnName($to);

            $qs->join($type, $table, $on);

            // Has many relations (we must work only with current model lines - exclude duplicates)
//            if (isset($join['group']) && ($join['group']) && !$this->_chainedHasMany) {
//                $this->_chainedHasMany = true;
//            }

            $alias = $newAlias;
        }
        return [$relatedModel, $alias];
    }
}
