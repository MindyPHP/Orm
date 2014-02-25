<?php

/**
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 03/01/14.01.2014 22:10
 */

namespace Mindy\Orm;


use ArrayAccess;
use Exception;

use Mindy\Query\Connection;

use Mindy\Orm\Traits\Fields;
use Mindy\Orm\Traits\Migrations;
use Mindy\Orm\Traits\YiiCompatible;
use Mindy\Query\OrmQuery;
use Mindy\Query\Query;

class Base implements ArrayAccess
{
    use Fields, Migrations, YiiCompatible;

    /**
     * @var bool Returns a value indicating whether the current record is new.
     */
    public $isNewRecord = true;

    /**
     * @var \Mindy\Query\Connection
     */
    private static $_connection;

    /**
     * @param Connection $connection
     */
    public static function setConnection(Connection $connection)
    {
        self::$_connection = $connection;
    }

    /**
     * @return \Mindy\Query\Connection
     */
    public static function getConnection()
    {
        return self::$_connection;
    }

    /**
     * @return string the fully qualified name of this class.
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Return table name based on this class name.
     * Override this method for custom table name.
     * @return string
     */
    public static function tableName()
    {
        $className = get_called_class();
        $normalizeClass = rtrim(str_replace('\\', '/', $className), '/\\');
        if (($pos = mb_strrpos($normalizeClass, '/')) !== false) {
            $class = mb_substr($normalizeClass, $pos + 1);
        } else {
            $class = $normalizeClass;
        }
        return trim(strtolower(preg_replace('/(?<![A-Z])[A-Z]/', '_\0', $class)), '_');
    }

    /**
     * Returns the schema information of the DB table associated with this AR class.
     * @return Mindy\Query\TableSchema the schema information of the DB table associated with this AR class.
     * @throws Exception if the table for the AR class does not exist.
     */
    public static function getTableSchema()
    {
        $schema = self::getConnection()->getTableSchema(static::tableName());
        if ($schema !== null) {
            return $schema;
        } else {
            throw new Exception("The table does not exist: " . static::tableName());
        }
    }

    /**
     * Creates an active record instance.
     * This method is called by [[create()]].
     * You may override this method if the instance being created
     * depends on the row data to be populated into the record.
     * For example, by creating a record based on the value of a column,
     * you may implement the so-called single-table inheritance mapping.
     * @param array $row row data to be populated into the record.
     * @return \Mindy\Orm\Model the newly created active record
     */
    public static function instantiate($row)
    {
        return new static;
    }

    /**
     * Creates an active record object using a row of data.
     * This method is called by [[ActiveQuery]] to populate the query results
     * into Active Records. It is not meant to be used to create new records.
     * @param array $row attribute values (name => value)
     * @return \Mindy\Orm\Model the newly created active record.
     */
    public static function create($row)
    {
        $record = static::instantiate($row);
        foreach ($row as $name => $value) {
            if ($record->hasField($name)) {
                $record->getField($name)->setValue($value);
            }
        }
        // TODO afterFind event
        return $record;
    }

    /**
     * Refresh primary key value after save model
     * @return void
     */
    protected function refreshPrimaryKeyValue()
    {
        $table = $this->getTableSchema();
        if ($table->sequenceName !== null) {
            foreach ($table->primaryKey as $name) {
                $field = $this->getField($name, false);
                if ($field->getValue() === null) {
                    $id = $this->getConnection()->getLastInsertID($table->sequenceName);
                    $field->setValue($id);
                    break;
                }
            }
        }
    }

    protected function insert()
    {
        $values = $this->getChangedValues();

        $connection = $this->getConnection();

        $command = $connection->createCommand()->insert(static::tableName(), $values);
        if(!$command->execute()) {
            return false;
        }

        $this->isNewRecord = false;
        $this->refreshPrimaryKeyValue();

        return true;
    }

    /**
     * Returns the name of the column that stores the lock version for implementing optimistic locking.
     *
     * Optimistic locking allows multiple users to access the same record for edits and avoids
     * potential conflicts. In case when a user attempts to save the record upon some staled data
     * (because another user has modified the data), a [[StaleObjectException]] exception will be thrown,
     * and the update or deletion is skipped.
     *
     * Optimistic locking is only supported by [[update()]] and [[delete()]].
     *
     * To use Optimistic locking:
     *
     * 1. Create a column to store the version number of each row. The column type should be `BIGINT DEFAULT 0`.
     *    Override this method to return the name of this column.
     * 2. In the Web form that collects the user input, add a hidden field that stores
     *    the lock version of the recording being updated.
     * 3. In the controller action that does the data updating, try to catch the [[StaleObjectException]]
     *    and implement necessary business logic (e.g. merging the changes, prompting stated data)
     *    to resolve the conflict.
     *
     * @return string the column name that stores the lock version of a table row.
     * If null is returned (default implemented), optimistic locking will not be supported.
     */
    public function optimisticLock()
    {
        return null;
    }

    /**
     * Updates the whole table using the provided attribute values and conditions.
     * For example, to change the status to be 1 for all customers whose status is 2:
     *
     * ~~~
     * Customer::updateAll(['status' => 1], 'status = 2');
     * ~~~
     *
     * @param array $attributes attribute values (name-value pairs) to be saved into the table
     * @param string|array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
     * Please refer to [[Query::where()]] on how to specify this parameter.
     * @param array $params the parameters (name => value) to be bound to the query.
     * @return integer the number of rows updated
     */
    public static function updateAll($attributes, $condition = '', $params = [])
    {
        $command = static::getConnection()->createCommand();
        $command->update(static::tableName(), $attributes, $condition, $params);
        return $command->execute();
    }

    protected function getChangedValues()
    {
        $values = [];
        foreach($this->getFieldsInit() as $name => $field) {
            if(is_a($field, $this->manyToManyField)) {
                continue;
            }

            if(is_a($field, $this->foreignField)) {
                $name .= '_id';
                /* @var $field \Mindy\Orm\Fields\ForeignField */
                $value = $field->getValue()->pk;
            } else {
                /* @var $field \Mindy\Orm\Fields\Field */
                $value = $field->getValue();
            }

            $values[$name] = $value;
        }

        return $values;
    }

    protected function update()
    {
        // TODO beforeSave
        $values = $this->getChangedValues();

        $name = $this->primaryKey();
        $condition = [];
        $condition[$name] = $this->getField($name)->getValue();

        $lock = $this->optimisticLock();
        if ($lock !== null) {
            if (!isset($values[$lock])) {
                $values[$lock] = $this->$lock + 1;
            }
            $condition[$lock] = $this->$lock;
        }

        // We do not check the return value of updateAll() because it's possible
        // that the UPDATE statement doesn't change anything and thus returns 0.
        $rows = $this->updateAll($values, $condition);

        if ($lock !== null && !$rows) {
            throw new Exception('The object being updated is outdated.');
        }
        return (bool) $rows;
    }

    public function save()
    {
        return $this->isNewRecord ? $this->insert() : $this->update();
    }

    /**
     * Returns the primary key name(s) for this AR class.
     * The default implementation will return the primary key(s) as declared
     * in the DB table that is associated with this AR class.
     *
     * If the DB table does not declare any primary key, you should override
     * this method to return the attributes that you want to use as primary keys
     * for this AR class.
     *
     * Note that an array should be returned even for a table with single primary key.
     *
     * @return string[] the primary keys of the associated database table.
     */
    public static function primaryKey()
    {
        $model = new self;
        return $model->getPkName();
        // return static::getTableSchema()->primaryKey;
    }

    // TODO documentation, refactoring
    public function getPkName()
    {
        foreach ($this->getFieldsInit() as $name => $field) {
            if (is_a($field, $this->autoField)) {
                return $name;
            }
        }

        return null;
    }

    public static function createQuery()
    {
        return new OrmQuery([
            'modelClass' => get_called_class()
        ]);
    }

    public static function find($q = null)
    {
        $query = static::createQuery();
        if (is_array($q)) {
            return $query->andWhere($q)->one();
        } elseif ($q !== null) {
            // query by primary key
            $primaryKey = static::primaryKey();
            if (isset($primaryKey[0])) {
                return $query->andWhere([$primaryKey[0] => $q])->one();
            } else {
                throw new Exception(get_called_class() . ' must have a primary key.');
            }
        }
        return $query;
    }

    public static function objects()
    {
        $className = get_called_class();
        return new Manager(new $className);
    }

    /**
     * Returns whether there is an element at the specified offset.
     * This method is required by the SPL interface `ArrayAccess`.
     * It is implicitly called when you use something like `isset($model[$offset])`.
     * @param mixed $offset the offset to check on
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->$offset !== null;
    }

    /**
     * Returns the element at the specified offset.
     * This method is required by the SPL interface `ArrayAccess`.
     * It is implicitly called when you use something like `$value = $model[$offset];`.
     * @param mixed $offset the offset to retrieve element.
     * @return mixed the element at the offset, null if no element is found at the offset
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Sets the element at the specified offset.
     * This method is required by the SPL interface `ArrayAccess`.
     * It is implicitly called when you use something like `$model[$offset] = $item;`.
     * @param integer $offset the offset to set element
     * @param mixed $item the element value
     */
    public function offsetSet($offset, $item)
    {
        $this->$offset = $item;
    }

    /**
     * Sets the element value at the specified offset to null.
     * This method is required by the SPL interface `ArrayAccess`.
     * It is implicitly called when you use something like `unset($model[$offset])`.
     * @param mixed $offset the offset to unset element
     */
    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }
}
