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
     * @return int
     */
    public function createTable(Model $model)
    {
        $sql = [];
        $columns = [];
        $schema = $this->getDb()->getSchema();
        foreach ($model->getFieldsInit() as $name => $field) {
            if ($field->sqlType() !== false) {
                if (is_a($field, OneToOneField::class) && $field->reversed) {
                    continue;
                } elseif (is_a($field, ForeignField::class)) {
                    $name .= "_id";
                }

                $columns[$name] = $field->sql();
            } else if (is_a($field, ManyToManyField::class)) {
                /* @var $field \Mindy\Orm\Fields\ManyToManyField */
                if (!$this->hasTable($field->getTableName())) {
                    if ($field->through === null) {
                        $fieldColumns = array_map(function ($column) use ($schema) {
                            return $schema->getColumnType($column);
                        }, $field->getColumns());
                        $sql[] = $this->getQueryBuilder()->createTable($field->getTableName(), $fieldColumns, null, true);
                    }
                }
            }
        }

        $mainColumns = array_map(function ($column) use ($schema) {
            return $schema->getColumnType($column);
        }, $columns);
        $sql[] = $this->getQueryBuilder()->createTable($model->tableName(), $mainColumns, null, true);

        $this->getDb()->createCommand(implode(";\n\n", $sql))->execute();
    }

    /**
     * @param $model \Mindy\Orm\Model
     * @return int
     */
    public function dropTable(Model $model)
    {
        $sql = [];
        foreach ($model->getManyFields() as $field) {
            if ($field->through === null) {
                if ($this->hasTable($field->getTableName())) {
                    $sql[] = $this->getQueryBuilder()->dropTable($field->getTableName(), true);
                }
            }
        }
        $sql[] = $this->getQueryBuilder()->dropTable($model->tableName(), true);
        $this->getDb()->createCommand(implode(";\n\n", $sql))->execute();
    }

    /**
     * @return array
     */
    public function create()
    {
        $created = [];
        foreach ($this->_models as $model) {
            if ($this->hasTable($model->tableName()) === false) {
                $this->createTable($model);
                $created[] = $model->tableName();
            }
        }
        return $created;
    }

    /**
     * Drop all tables from database
     * @return array
     */
    public function delete()
    {
        $deleted = [];
        foreach ($this->_models as $model) {
            if ($this->hasTable($model->tableName())) {
                $this->dropTable($model);
                $deleted[] = $model->tableName();
            }
        }
        return $deleted;
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
