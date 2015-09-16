<?php

namespace Mindy\Orm;

use Mindy\Exception\NotSupportedException;
use Mindy\Query\ConnectionManager;

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
                if (is_a($field, $model::$oneToOneField) && $field->reversed) {
                    continue;
                } elseif (is_a($field, $model::$foreignField)) {
                    $name .= "_id";
                }

                $columns[$name] = $field->sql();
            } else if (is_a($field, $model::$manyToManyField)) {
                /* @var $field \Mindy\Orm\Fields\ManyToManyField */
                if (!$this->hasTable($model, $field->getTableName())) {
                    if ($field->through === null) {
                        $command->createTable($field->getTableName(), $field->getColumns())->execute();
                    }
                }
            }
        }

        return $command->createTable($model->tableName(), $columns)->execute();
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
                if ($this->hasTable($model, $field->getTableName())) {
                    $command->dropTable($field->getTableName())->execute();
                }
            }
        }
        return $command->dropTable($model->tableName())->execute();

        /*
        try {
            // TODO checkIntegrity is not supported by SQLite
            // $this->db->createCommand()->checkIntegrity(true)->execute();
        } catch (NotSupportedException $e) {

        }
        */
    }

    /**
     * @param $model \Mindy\Orm\Model
     */
    public function createIndexes(Model $model)
    {
        $command = $this->db->createCommand();

        try {
            // checkIntegrity is not supported by SQLite
            // $command->checkIntegrity(false)->execute();
        } catch (NotSupportedException $e) {

        }

        foreach ($model->getFields() as $name => $field) {
            if (is_a($field, '\Mindy\Orm\Fields\ForeignField')) {
                /* @var $modelClass \Mindy\Orm\Model */
                /* @var $field \Mindy\Orm\Fields\ForeignField */
                $modelClass = $field->modelClass;
                $fkModel = new $modelClass();
                $command->addForeignKey(
                    "fk_{$name}",
                    $model->tableName(), [$name . '_id'],
                    $modelClass::tableName(), [$fkModel->getPkName()],
                    $delete = $field->getOnDelete(),
                    $update = $field->getOnUpdate()
                );
                $command->execute();
            }
        }

        try {
            // checkIntegrity is not supported by SQLite
            // $command->checkIntegrity(true)->execute();
        } catch (NotSupportedException $e) {

        }
    }

    /**
     * @param $model \Mindy\Orm\Model
     */
    public function dropIndexes(Model $model)
    {
        $command = $this->db->createCommand();

        try {
            // checkIntegrity is not supported by SQLite
            // $command->checkIntegrity(false)->execute();
        } catch (NotSupportedException $e) {

        }

        foreach ($model->getFields() as $name => $field) {
            if (is_a($field, '\Mindy\Orm\Fields\ForeignField')) {
                /* @var $modelClass Orm */
                /* @var $field \Mindy\Orm\Fields\ForeignField */
                // $modelClass = $field->relation->modelClass;
                $command->dropForeignKey("fk_{$name}", $model::tableName());
            }
        }

        try {
            // checkIntegrity is not supported by SQLite
            // $command->checkIntegrity(true)->execute();
        } catch (NotSupportedException $e) {

        }
    }

    /**
     * @return array
     */
    public function create()
    {
        $created = [];
        foreach ($this->_models as $model) {
            if (!$this->hasTable($model) && $this->createTable($model)) {
                $created[] = $model->tableName();
            }
        }

        foreach ($this->_models as $model) {
            $this->createIndexes($model);
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
            if ($this->hasTable($model)) {
                $this->dropIndexes($model);
                if ($this->dropTable($model)) {
                    $deleted[] = $model->tableName();
                }
            }
        }

        return $deleted;
    }

    /**
     * Check table in database.
     * @param $model \Mindy\Orm\Model
     * @param null $tableName
     * @return bool
     */
    public function hasTable($model, $tableName = null)
    {
        if ($tableName === null) {
            $tableName = $model->tableName();
        }
        $schema = $this->db->schema;
        $rawTableName = $schema->getRawTableName($tableName);
        $tables = $schema->getTableNames('', true);
        return in_array($rawTableName, $tables);
    }
}
