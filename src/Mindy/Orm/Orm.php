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
 * @date 03/01/14.01.2014 21:52
 */

namespace Mindy\Orm;


use Exception;
use Mindy\Core\Interfaces\Arrayable;
use Mindy\Helper\Creator;
use Mindy\Helper\Json;
use Mindy\Query\Connection;

/**
 * Class Orm
 * @package Mindy\Orm
 * @method static \Mindy\Orm\Manager objects($instance = null)
 */
class Orm extends Base implements Arrayable
{
    /**
     * @var array
     */
    private $_attributes = [];

    /**
     * @var array validation errors (attribute name => array of errors)
     */
    private $_errors = [];

    /**
     * TODO move to manager
     * @var \Mindy\Query\Connection
     */
    private static $_connection;

    /**
     * @var string
     */
    public $autoField = '\Mindy\Orm\Fields\AutoField';

    /**
     * @var string
     */
    public $relatedField = '\Mindy\Orm\Fields\RelatedField';

    /**
     * @var string
     */
    public $foreignField = '\Mindy\Orm\Fields\ForeignField';

    /**
     * TODO
     * @var string
     */
    public $oneToOneField = '\Mindy\Orm\Fields\OneToOneField';

    /**
     * @var string
     */
    public $manyToManyField = '\Mindy\Orm\Fields\ManyToManyField';

    /**
     * @var string
     */
    public $hasManyField = '\Mindy\Orm\Fields\HasManyField';

    /**
     * @var array
     */
    private $_fields = [];

    /**
     * @var array
     * @deprecated
     */
    private $_oldFields = [];

    /**
     * @var array
     */
    private $_manyFields = [];

    /**
     * @var array
     */
    private $_hasManyFields = [];

    /**
     * @var array
     * instead of $this->_oldFields
     */
    private $_oldValues = [];

    /**
     * TODO move to manager
     * Creates an active record object using a row of data.
     * This method is called by [[ActiveQuery]] to populate the query results
     * into Active Records. It is not meant to be used to create new records.
     * @param array $row attribute values (name => value)
     * @return \Mindy\Orm\Model the newly created active record.
     */
    public static function create($row)
    {
        $className = self::className();
        $record = new $className;
        $record->setAttributes($row);
        $record->setOldAttributes($row);
        return $record;
    }

    /**
     * TODO move to manager
     * Refresh primary key value after save model
     * @return void
     */
    protected function refreshPrimaryKeyValue()
    {
        $table = $this->getTableSchema();
        if ($table->sequenceName !== null) {
            foreach ($table->primaryKey as $name) {
                $field = $this->getField($name, false);
                if ($field && $field->getValue() === null) {
                    $field->setValue($this->getConnection()->getLastInsertID($table->sequenceName));
                    break;
                }
            }
        }
    }

    /**
     * TODO move to manager
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

    /**
     * @return array
     * @deprecated
     */
    public function getChangedFields()
    {
        return $this->_oldFields;
    }

    public function getOldValues()
    {
        return $this->_oldValues;
    }

    public function setOldValues()
    {
        foreach ($this->_fields as $name => $field) {
            if (is_a($field, $this->manyToManyField) || is_a($field, $this->hasManyField)) {
                continue;
            }

            if (is_a($field, $this->foreignField)) {
                $newName = $name . '_id';
                /* @var $field \Mindy\Orm\Fields\ForeignField */
                $value = $field->getDbPrepValue();
                if (is_a($value, '\Mindy\Orm\Model')) {
                    $value = $value->pk;
                }
            } else {
                $newName = $name;
                /* @var $field \Mindy\Orm\Fields\Field */
                $value = $field->getDbPrepValue();
            }

            $this->_oldValues[$newName] = $value;
        }
    }

    /**
     * TODO method work incorrect
     * @param array $fields return incoming fields only
     * @return array
     */
    public function getChangedValues(array $fields = [])
    {
        $values = [];
        $rawFields = $this->getFieldsInit();

        if (!empty($fields)) {
            $initFields = [];
            foreach ($fields as $field) {
                $initFields[$field] = $rawFields[$field];
            }
        } else {
            $initFields = $rawFields;
        }

        $oldValues = $this->getOldValues();

        foreach ($initFields as $name => $field) {
            if (is_a($field, $this->manyToManyField) || is_a($field, $this->hasManyField)) {
                continue;
            }

            if (is_a($field, $this->foreignField)) {
                $newName = $name . '_id';
                /* @var $field \Mindy\Orm\Fields\ForeignField */
                $value = $field->getDbPrepValue();
                if (is_a($value, '\Mindy\Orm\Model')) {
                    $value = $value->pk;
                }
            } else {
                $newName = $name;
                /* @var $field \Mindy\Orm\Fields\Field */
                $value = $field->getDbPrepValue();
            }

            if ($this->getIsNewRecord() || !array_key_exists($newName, $oldValues) || (array_key_exists($newName, $oldValues) && $oldValues[$newName] !== $value)) {
                $values[$newName] = $value;
            }
        }

        return $values;
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    // TODO documentation, refactoring
    public function getPkName()
    {
        foreach ($this->getFieldsInit() as $name => $field) {
            if (is_a($field, $this->autoField) || $field->primary) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Adds a new error to the specified attribute.
     * @param string $attribute attribute name
     * @param string $error new error message
     */
    public function addError($attribute, $error = '')
    {
        $this->_errors[$attribute][] = $error;
    }

    /**
     * Removes errors for all attributes or a single attribute.
     * @param string $attribute attribute name. Use null to remove errors for all attribute.
     */
    public function clearErrors($attribute = null)
    {
        if ($attribute === null) {
            $this->_errors = [];
        } else {
            unset($this->_errors[$attribute]);
        }
    }

    /**
     * Returns a value indicating whether there is any validation error.
     * @param string|null $attribute attribute name. Use null to check all attributes.
     * @return boolean whether there is any error.
     */
    public function hasErrors($attribute = null)
    {
        return $attribute === null ? !empty($this->_errors) : isset($this->_errors[$attribute]);
    }

    /**
     * Returns the errors for all attribute or a single attribute.
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @property array An array of errors for all attributes. Empty array is returned if no error.
     * The result is a two-dimensional array. See [[getErrors()]] for detailed description.
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     * Note that when returning errors for all attributes, the result is a two-dimensional array, like the following:
     *
     * ~~~
     * [
     *     'username' => [
     *         'Username is required.',
     *         'Username must contain only word characters.',
     *     ],
     *     'email' => [
     *         'Email address is invalid.',
     *     ]
     * ]
     * ~~~
     *
     * @see getFirstErrors()
     * @see getFirstError()
     */
    public function getErrors($attribute = null)
    {
        if ($attribute === null) {
            return $this->_errors === null ? [] : $this->_errors;
        } else {
            return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : [];
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $this->clearErrors();

        /* @var $field \Mindy\Orm\Fields\Field */
        foreach ($this->getFieldsInit() as $name => $field) {
            if ($field->isValid() === false) {
                foreach ($field->getErrors() as $error) {
                    $this->addError($name, $error);
                }
            }
        }

        return $this->hasErrors() === false;
    }

    /**
     * @deprecated
     * @see setOldValues
     */
    protected function setOldFields()
    {
        foreach ($this->_fields as $name => $field) {
            $this->_oldFields[$name] = clone $field;
        }
    }

    public function hasManyToManyField($name)
    {
        return $this->meta->hasManyToManyField($this->className(), $name);
    }

    public function hasHasManyField($name)
    {
        return $this->meta->hasHasManyField($this->className(), $name);
    }

    /**
     * @return \Mindy\Orm\Fields\HasManyField[]
     */
    public function getHasManyFields()
    {
        return $this->_hasManyFields;
    }

    /**
     * Return initialized old fields
     * @return \Mindy\Orm\Fields\Field[]
     * @deprecated
     */
    public function getOldFieldsInit()
    {
        return $this->_oldFields;
    }

    /**
     * Example usage:
     * return [
     *     'name' => new CharField(['length' => 250, 'default' => '']),
     *     'email' => new EmailField(),
     * ]
     * @return array
     */
    public function getFields()
    {
        return [];
    }

    /**
     * Converts the object into an array.
     * @return array the array representation of this object
     */
    public function toArray()
    {
        return $this->_attributes;
    }

    public function toJson()
    {
        return Json::encode($this->toArray());
    }
}
