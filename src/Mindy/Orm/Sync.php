<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 24/02/14.02.2014 20:27
 */

namespace Mindy\Orm;


use Mindy\Exception\NotSupportedException;
use Mindy\Query\ConnectionManager;

class Sync
{
    private $_models = [];

    public function __construct($models)
    {
        if (!is_array($models)) {
            $models = [$models];
        }
        $this->_models = $models;
        $this->db = ConnectionManager::getDb();
    }

    /**
     * @param $model \Mindy\Orm\Model
     */
    public function createTable(Model $model)
    {
        $columns = [];
        $command = $this->db->createCommand();
        foreach ($model->getFieldsInit() as $name => $field) {
            if ($field->sqlType() !== false) {
                if (is_a($field, $model::$foreignField)) {
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

        $command->createTable($model->tableName(), $columns)->execute();
    }

    /**
     * @param $model \Mindy\Orm\Model
     */
    public function dropTable(Model $model)
    {
        $command = $this->db->createCommand();

        try {
            // checkIntegrity is not supported by SQLite
            $command->checkIntegrity(false)->execute();
        } catch (NotSupportedException $e) {

        }

        foreach ($model->getManyFields() as $field) {
            if ($field->through === null) {
                if ($this->hasTable($model, $field->getTableName())) {
                    $command->dropTable($field->getTableName())->execute();
                }
            }
        }
        $command->dropTable($model->tableName())->execute();
        $this->db->getTableSchema($model->tableName(), true);

        try {
            // checkIntegrity is not supported by SQLite
            $command->checkIntegrity(true)->execute();
        } catch (NotSupportedException $e) {

        }
    }

    /**
     * @param $model \Mindy\Orm\Model
     */
    public function createIndexes(Model $model)
    {
        $command = $this->db->createCommand();

        try {
            // checkIntegrity is not supported by SQLite
            $command->checkIntegrity(false)->execute();
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
            $command->checkIntegrity(true)->execute();
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
            $command->checkIntegrity(false)->execute();
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
            $command->checkIntegrity(true)->execute();
        } catch (NotSupportedException $e) {

        }
    }

    public function create()
    {
        foreach ($this->_models as $model) {
            if (!$this->hasTable($model)) {
                $this->createTable($model);
            }
        }

        foreach ($this->_models as $model) {
            $this->createIndexes($model);
            $this->db->getTableSchema($model->tableName(), true);
        }

        return $this;
    }

    /**
     * Drop all tables from database
     * @return $this
     */
    public function delete()
    {
        foreach ($this->_models as $model) {
            if ($this->hasTable($model)) {
                $this->dropIndexes($model);
                $this->dropTable($model);
            }
        }

        return $this;
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
        return !is_null($this->db->getTableSchema($tableName, true));
    }
}
