<?php

namespace Mindy\Orm;

use Mindy\Exception\NotSupportedException;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\OneToOneField;
use Mindy\Query\Connection;
use Mindy\Query\Schema\TableSchema;

/**
 * Class Sync
 * @package Mindy\Orm
 */
class Sync
{
    /**
     * @var \Mindy\Orm\Model[]
     */
    private $_models = [];
    /**
     * @var \Mindy\Query\Connection
     */
    private $_db;
    /**
     * @var \Mindy\QueryBuilder\QueryBuilder
     */
    private $_qb;

    public function __construct($models, Connection $db)
    {
        if (!is_array($models)) {
            $models = [$models];
        }
        $this->_models = $models;
        $this->_db = $db;
        $this->_qb = $db->getQueryBuilder();
    }

    /**
     * @return Connection
     */
    protected function getDb()
    {
        return $this->_db;
    }

    /**
     * @return \Mindy\QueryBuilder\QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return $this->_qb;
    }

    /**
     * @param $model \Mindy\Orm\Model
     * @return array
     */
    public function createTable(Model $model)
    {
        $sql = [];
        $columns = [];
        $schema = $this->getDb()->getSchema();
        foreach ($model->getFieldsInit() as $name => $field) {
            if (is_a($field, OneToOneField::class) && $field->reversed) {
                continue;
            }

            $field->setModel($model);
            $sqlColumn = $field->getSql($schema);
            if (empty($sqlColumn) === false) {
                if ($field instanceof ForeignField) {
                    $name .= "_id";
                }

                $columns[$name] = $sqlColumn;
            } else if ($field instanceof ManyToManyField) {
                /* @var $field \Mindy\Orm\Fields\ManyToManyField */
                if ($field->through === null) {
                    $fieldColumns = $field->getColumns($schema);
                    $sql[] = $this->getQueryBuilder()->createTable($field->getTableName(), $fieldColumns, null, true);
                }
            }
        }

        $mainColumns = array_map(function ($column) use ($schema) {
            return $schema->getColumnType($column);
        }, $columns);
        $sql[] = $this->getQueryBuilder()->createTable($model->tableName(), $mainColumns, null, true);
        return $sql;
    }

    /**
     * @param $model \Mindy\Orm\Model
     * @return array
     */
    public function dropTable(Model $model)
    {
        $sql = [];
        foreach ($model->getManyFields() as $field) {
            if ($field->through === null) {
                $sql[] = $this->getQueryBuilder()->dropTable($field->getTableName(), true);
            }
        }
        $sql[] = $this->getQueryBuilder()->dropTable($model->tableName(), true);
        return $sql;
    }

    /**
     * @return int
     */
    public function create()
    {
        $i = 0;
        foreach ($this->_models as $model) {
            $sql = $this->createTable($model);
            foreach ($sql as $part) {
                $state = $this->getDb()->createCommand($part)->execute(true);
                if ($state) {
                    $i += 1;
                }
            }
        }
        return $i;
    }

    /**
     * @return array
     */
    public function createSql()
    {
        $sql = [];
        foreach ($this->_models as $model) {
            $sql[] = $this->createTable($model);
        }
        return $sql;
    }

    /**
     * Drop all tables from database
     * @return int
     */
    public function delete()
    {
        $i = 0;
        foreach ($this->_models as $model) {
            $sql = $this->dropTable($model);
            foreach ($sql as $part) {
                $state = $this->getDb()->createCommand($part)->execute(true);
                if ($state) {
                    $i += 1;
                }
            }
        }
        return $i;
    }

    /**
     * Drop all tables from database
     * @return array
     */
    public function deleteSql()
    {
        $sql = [];
        foreach ($this->_models as $model) {
            $sql[] = $this->dropTable($model);
        }
        return $sql;
    }

    /**
     * Check table in database.
     * @param null $tableName
     * @return bool
     */
    public function hasTable($tableName)
    {
        if ($tableName instanceof Model) {
            $tableName = $tableName->tableName();
        }
        return $this->getDb()->getSchema()->getTableSchema($tableName, true) instanceof TableSchema;
    }
}
