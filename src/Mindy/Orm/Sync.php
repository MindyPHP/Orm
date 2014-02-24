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


class Sync
{
    private $_models = [];

    public function __construct($models)
    {
        if (!is_array($models)) {
            $models = [$models];
        }
        $this->_models = $models;
    }

    /**
     * @param $model
     */
    public function createTable($model)
    {
        /* @var $model \Mindy\Orm\Orm */
        /* @var $field \Mindy\Orm\Fields\Field */
        $columns = [];
        /* @var $command \yii\db\Command */
        $command = $model->getConnection()->createCommand();
        foreach ($model->getFieldsInit() as $name => $field) {
            if ($field->sqlType() !== false) {
                if (is_a($field, $model->foreignField)) {
                    $name .= "_id";
                }

               $columns[$name] = $field->sql();
            } else if(is_a($field, $model->manyToManyField)) {
                /* @var $field \Mindy\Orm\Fields\ManyToManyField */
                if(!$this->hasTable($model, $field->getTableName())) {
                    if($field->through === null) {
                        $command->createTable($field->getTableName(), $field->getColumns())->execute();
                    } else {
                        $this->createTable($field->through);
                    }
                }
            }
        }

        $command->createTable($model->tableName(), $columns)->execute();
    }

    /**
     * @param $model
     */
    public function dropTable($model)
    {
        $connection = $model->getConnection();
        /* @var $model \Mindy\Orm\Orm */
        foreach($model->getManyFields() as $field) {
            /* @var $model \Mindy\Orm\Fields\ManyToManyField */
            if($field->through === null) {
                $connection->createCommand()->dropTable($field->getTableName())->execute();
            } else {
                $this->dropTable($field->through);
            }
        }
        $connection->createCommand()->dropTable($model->tableName())->execute();
    }

    /**
     * TODO
     */
    public function createIndexes($model)
    {
//        $command = $model->getDb()->createCommand();
//
//        $command->checkIntegrity(false)->execute();
//
//        foreach($model->getFields() as $name => $field) {
//            if(is_a($field, 'ForeignField')) {
//                /* @var $modelClass Orm */
//                /* @var $field ForeignField */
//                $modelClass = $field->relation->modelClass;
//                foreach($field->relation->link as $currentColumn => $fkColumn) {
//                    $command->addForeignKey(
//                        "fk_{$name}",
//                        $model->tableName(), [$currentColumn],
//                        $modelClass::tableName(), [$fkColumn],
//                        $delete = $field->getOnDelete(),
//                        $update = $field->getOnUpdate()
//                    );
//                    $command->execute();
//                }
//            }
//        }
//
//        $command->checkIntegrity(true)->execute();
    }

    /**
     * TODO
     */
    public function dropIndexes()
    {
//        $command = self::$db->createCommand();
//
//        $command->checkIntegrity(false)->execute();
//
//        foreach($this->getFields() as $name => $field) {
//            if(is_a($field, 'ForeignField')) {
//                /* @var $modelClass Orm */
//                /* @var $field ForeignField */
//                // $modelClass = $field->relation->modelClass;
//                $command->dropForeignKey("fk_{$name}", self::tableName());
//            }
//        }
//
//        $command->checkIntegrity(true)->execute();
    }

    public function create()
    {
        foreach ($this->_models as $model) {
            if(!$this->hasTable($model)) {
                $this->createTable($model);
            }
        }

        foreach ($this->_models as $model) {
            $this->createIndexes($model);
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
            if($this->hasTable($model)) {
                $this->dropTable($model);
            }
        }

        return $this;
    }

    /**
     * Check table in database.
     * @param $model
     * @return bool
     */
    public function hasTable($model, $tableName = null)
    {
        if($tableName === null) {
            $tableName = $model->tableName();
        }
        return !is_null($model->getConnection()->getTableSchema($tableName, true));
    }
}
