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
 * @date 03/01/14.01.2014 22:49
 */

namespace Mindy\Db\Traits;

/**
 * Class Migrations
 * @package Mindy\Db\Traits
 */
trait Migrations
{
    /**
     * @return \yii\db\Command
     */
    public function createTable()
    {
        /* @var $this \Mindy\Db\Orm */
        /* @var $field \Mindy\Db\Fields\Field */
        $columns = [];
        foreach ($this->getFieldsInit() as $name => $field) {
            if ($field->sqlType() !== false) {
                if(is_a($field, $this->foreignField)) {
                    $name .= "_id";
                }

                $columns[$name] = $field->sql();
            }
        }

        return $this->getConnection()->createCommand()->createTable($this->tableName(), $columns);
    }

    /**
     * @return \yii\db\Command
     */
    public function dropTable()
    {
        /* @var $this \Mindy\Db\Orm */
        return $this->getConnection()->createCommand()->dropTable($this->tableName());
    }

    /**
     * TODO
     */
    public function createIndexes()
    {
//        $command = self::$db->createCommand();
//        foreach($this->getFields() as $name => $field) {
//            if(is_a($field, 'ForeignField')) {
//                /* @var $modelClass Orm */
//                /* @var $field ForeignField */
//                $modelClass = $field->relation->modelClass;
//                foreach($field->relation->link as $currentColumn => $fkColumn) {
//                    $command->addForeignKey(
//                        "fk_{$name}",
//                        self::tableName(), [$currentColumn],
//                        $modelClass::tableName(), [$fkColumn],
//                        $delete = $field->getOnDelete(),
//                        $update = $field->getOnUpdate()
//                    );
//                    $command->execute();
//                }
//            }
//        }
    }

    /**
     * TODO
     */
    public function dropIndexes()
    {
//        $command = self::$db->createCommand();
//        foreach($this->getFields() as $name => $field) {
//            if(is_a($field, 'ForeignField')) {
//                /* @var $modelClass Orm */
//                /* @var $field ForeignField */
//                // $modelClass = $field->relation->modelClass;
//                $command->dropForeignKey("fk_{$name}", self::tableName());
//            }
//        }
    }
}
