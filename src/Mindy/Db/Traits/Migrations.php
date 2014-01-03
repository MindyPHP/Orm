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
}
