<?php

namespace Mindy\Orm;

use Mindy\Exception\NotSupportedException;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\OneToOneField;
use Mindy\Query\ConnectionManager;
use Mindy\Query\TableSchema;

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

    public function __construct($models, $db = null)
    {
        if (!is_array($models)) {
            $models = [$models];
        }
        $this->_models = $models;
        $this->db = ConnectionManager::getDb($db);
    }

    /**
     * @param $model \Mindy\Orm\Model
     * @return int
     */
    public function createTable(Model $model)
    {
        $columns = [];
        $command = $this->db->createCommand();
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
                        $command->createTable($field->getTableName(), $field->getColumns())->execute();
                    }
                }
            }
        }

        $command->createTable($model->tableName(), $columns)->execute();
    }

    /**
     * @param $model \Mindy\Orm\Model
     * @return int
     */
    public function dropTable(Model $model)
    {
        $command = $this->db->createCommand();

        try {
            // TODO checkIntegrity is not supported by SQLite
            // $command->checkIntegrity(false)->execute();
        } catch (NotSupportedException $e) {

        }

        foreach ($model->getManyFields() as $field) {
            if ($field->through === null) {
                if ($this->hasTable($field->getTableName())) {
                    $command->dropTable($field->getTableName())->execute();
                }
            }
        }
        $command->dropTable($model->tableName())->execute();

        /*
        try {
            // TODO checkIntegrity is not supported by SQLite
            // $this->db->createCommand()->checkIntegrity(true)->execute();
        } catch (NotSupportedException $e) {

        }
        */
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
        return $this->db->getSchema()->getTableSchema($tableName, true) instanceof TableSchema;
    }
}
