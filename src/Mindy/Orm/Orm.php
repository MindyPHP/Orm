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
     * @var bool if true, model created without initialized fields
     */
    public $autoInitFields = true;
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
     */
    private $_oldFields = [];

    /**
     * @var array
     */
    private $_fkFields = [];

    /**
     * @var array
     */
    private $_manyFields = [];

    /**
     * @var array
     */
    private $_hasManyFields = [];


    /**
     * TODO move to manager
     * @param Connection $connection
     */
    public static function setConnection(Connection $connection)
    {
        self::$_connection = $connection;
    }

    /**
     * TODO move to manager
     * @return \Mindy\Query\Connection
     */
    public static function getConnection()
    {
        return self::$_connection;
    }

    /**
     * TODO move to manager
     * Returns the schema information of the DB table associated with this AR class.
     * @return \Mindy\Query\TableSchema the schema information of the DB table associated with this AR class.
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
     * TODO move to manager
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
     * TODO move to manager
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
                if ($field->getValue() === null) {
                    $id = $this->getConnection()->getLastInsertID($table->sequenceName);
                    $field->setValue($id);
                    break;
                }
            }
        }
    }

    /**
     * TODO move to manager
     * @return bool
     */
    protected function insert(array $fields = [])
    {
        $values = $this->getChangedValues($fields);

        $connection = $this->getConnection();

        $command = $connection->createCommand()->insert(static::tableName(), $values);
        if (!$command->execute()) {
            return false;
        }

        $this->setOldFields();

        $this->refreshPrimaryKeyValue();

        return true;
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

    public function getChangedFields()
    {
        return $this->_oldFields;
    }

    /**
     * TODO method work incorrect
     * @param array $fields return incoming fields only
     * @return array
     */
    protected function getChangedValues(array $fields = [])
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

        $oldFields = $this->getOldFieldsInit();

        foreach ($initFields as $name => $field) {
            if (is_a($field, $this->manyToManyField) || is_a($field, $this->hasManyField)) {
                continue;
            }

            if (is_a($field, $this->foreignField)) {
                $newName = $name . '_id';
                /* @var $field \Mindy\Orm\Fields\ForeignField */
                $value = $field->getDbPrepValue();
                if(is_a($value, '\Mindy\Orm\Model')) {
                    $value = $value->pk;
                }
            } else {
                $newName = $name;
                /* @var $field \Mindy\Orm\Fields\Field */
                $value = $field->getDbPrepValue();
            }

            if ($this->getIsNewRecord()) {
                $values[$newName] = $value;
            } else {
                $oldField = $oldFields[$name];
                if (is_a($oldField, $this->foreignField)) {
                    /* @var $field \Mindy\Orm\Fields\ForeignField */
                    $oldValue = $oldField->getDbPrepValue();
                    if(is_a($oldValue, '\Mindy\Orm\Model')) {
                        $oldValue = $oldValue->pk;
                    }
                } else {
                    /* @var $field \Mindy\Orm\Fields\Field */
                    $oldValue = $oldField->getDbPrepValue();
                }

                if ($oldValue != $value) {
                    $values[$newName] = $value;
                }
            }
        }

        return $values;
    }

    /**
     * TODO move to manager
     * @return bool
     * @throws \Exception
     */
    protected function update(array $fields = [])
    {
        // TODO beforeSave
        $values = $this->getChangedValues($fields);

        $name = $this->primaryKey();
        $condition = [
            $name => $this->getField($name)->getValue()
        ];

        $this->setOldFields();
        // We do not check the return value of updateAll() because it's possible
        // that the UPDATE statement doesn't change anything and thus returns 0.
        return (bool)$this->updateAll($values, $condition);
    }

    /**
     * TODO move to manager
     * @return bool
     */
    public function save(array $fields = [])
    {
        return $this->getIsNewRecord() ? $this->insert($fields) : $this->update($fields);
    }

    public function delete()
    {
        if ($this->getIsNewRecord()) {
            throw new Exception("The node can't be deleted because it is new.");
        }

        return $this->objects()->delete([
            $this->primaryKey() => $this->pk
        ]);
    }

    public function getIsNewRecord()
    {
        return $this->pk === null;
    }

    /**
     * @return string|null
     */
    public static function primaryKey()
    {
        $className = get_called_class();
        $model = new $className();
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

    public static function __callStatic($method, $args)
    {
        $manager = $method . 'Manager';
        $className = get_called_class();
        if (is_callable([$className, $manager])) {
            return call_user_func_array([$className, $manager], $args);
        } elseif (is_callable([$className, $method])) {
            return call_user_func_array([$className, $method], $args);
        } else {
            throw new Exception("Call unknown method {$method}");
        }
    }

    public function __call($method, $args)
    {
        $manager = $method . 'Manager';
        if (method_exists($this, $manager)) {
            return call_user_func_array([$this, $manager], array_merge([$this], $args));
        } elseif (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $args);
        } else {
            throw new Exception("Call unknown method {$method}");
        }
    }

    public static function objectsManager($instance = null)
    {
        $className = get_called_class();
        return new Manager($instance ? $instance : new $className);
    }

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        Creator::configure($this, $config);
        if ($this->autoInitFields) {
            $this->initFields();
        }
    }

    /**
     * Sets value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$object->property = $value;`.
     * @param string $name the property name or the event name
     * @param mixed $value the property value
     * @throws \Exception
     * @see __set()
     */
    public function __set($name, $value)
    {
        if ($this->hasField($name)) {
            $field = $this->getField($name);
            if (is_a($field, $this->foreignField)) {
                /** @var $field \Mindy\Orm\Fields\ForeignField */
                $this->_fkFields[$name . '_' . $field->getForeignPrimaryKey()] = $name;
            }

            // Users: {'pk': 1, 'username': 'Max'}
            // $model = User::objects->filter(['pk' => 1])->get();
            // $model->username = 'Anton'; $model->username = 'Anton'; getChangedValues -> username not changed!

            //$this->_oldFields[$name] = clone $field;

            $field->setValue($value);
        } else if ($this->hasForeignKey($name)) {
            $field = $this->getForeignKey($name);

            //$this->_oldFields[$name] = clone $field;

            $field->setValue($value);
        } else if (false) {
            // TODO add support for m2m setter. Example:
            /**
             * $model->items = []; override all related records.
             */
        } else {
            throw new Exception('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Returns the value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$value = $object->property;`.
     * @param string $name
     * @return mixed the property value
     * @throws \Exception
     * @see __get()
     */
    public function __get($name)
    {
        if ($name == 'pk') {
            return $this->getPk();
        }

        if ($this->hasField($name)) {
            $field = $this->getField($name);
            if (is_a($field, $this->relatedField)) {
                if (is_a($field, $this->foreignField)) {
                    /* @var mixed */
                    return $field->getValue();
                } else if (is_a($field, $this->manyToManyField) || is_a($field, $this->hasManyField)) {
                    /* @var $field \Mindy\Orm\Fields\ManyToManyField|\Mindy\Orm\Fields\HasManyField */
                    return $field->getManager();
                } else {
                    throw new Exception("Unknown field type " . $name . " in " . get_class($this));
                }
            } else {
                return $field->getValue();
            }
        } else if ($this->hasForeignKey($name)) {
            return $this->getForeignKey($name)->getValue()->getPk();
        }

        throw new Exception('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    /**
     * @param $name
     * @return bool
     */
    private function hasForeignKey($name)
    {
        return array_key_exists($name, $this->_fkFields);
    }

    /**
     * @param $name
     * @return \Mindy\Orm\Fields\ForeignField
     */
    private function getForeignKey($name)
    {
        return $this->getField($this->_fkFields[$name]);
    }

    public function getPk()
    {
        /* @var $field \Mindy\Orm\Fields\Field */
        if ($this->hasField('id')) {
            return $this->getField('id')->getValue();
        } else {
            foreach ($this->getFieldsInit() as $name => $field) {
                if (is_a($field, $this->autoField)) {
                    return $field->getValue();
                }
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
     * Initialize fields
     * @void
     */
    public function initFields($fields = [], $extra = false)
    {
        if (empty($fields)) {
            $fields = $this->getFields();
        }

        $needPk = !$extra;

        foreach ($fields as $name => $config) {
            $field = Creator::createObject($config);
            $field->setName($name);
            $field->setModel($this);
            /* @var $field \Mindy\Orm\Fields\Field */
            if (is_a($field, $this->autoField)) {
                $needPk = false;
            }

            if (is_a($field, $this->relatedField)) {
                /* @var $field \Mindy\Orm\Fields\RelatedField */
                if (is_a($field, $this->manyToManyField)) {
                    /* @var $field \Mindy\Orm\Fields\ManyToManyField */
                    $this->_manyFields[$name] = $field;
                } else if (is_a($field, $this->hasManyField)) {
                    /* @var $field \Mindy\Orm\Fields\HasManyField */
                    $this->_hasManyFields[$name] = $field;
                } else if (is_a($field, $this->foreignField)) {
                    /* @var $field \Mindy\Orm\Fields\ForeignField */
                    $this->_fields[$name] = $field;

                    // ForeignKey in self model
                    if ($field->modelClass == get_class($this)) {
                        $this->_fkFields[$name . '_' . $this->getPkName()] = $name;
                    } else {
                        $this->_fkFields[$name . '_' . $field->getForeignPrimaryKey()] = $name;
                    }
                }
            } else {
                $this->_fields[$name] = $field;
            }

            if (!$extra) {
                $extraFields = $field->getExtraFields();
                if (!empty($extraFields)) {
                    $this->initFields($extraFields, true);
                    foreach ($extraFields as $key => $value) {
                        $field->setExtraField($key, $this->_fields[$key]);
                    }
                }
            }
        }

        if ($needPk) {
            $this->_fields = array_merge([
                'id' => new $this->autoField()
            ], $this->_fields);
        }

        foreach ($this->_manyFields as $name => $field) {
            /* @var $field \Mindy\Orm\Fields\ManyToManyField */
            $this->_fields[$name] = $field;
        }

        foreach ($this->_hasManyFields as $name => $field) {
            /* @var $field \Mindy\Orm\Fields\HasManyField */
            $this->_fields[$name] = $field;
        }

        $this->setOldFields();
    }

    protected function setOldFields()
    {
        foreach ($this->_fields as $name => $field) {
            $this->_oldFields[$name] = clone $field;
        }
    }

    public function hasManyToManyField($name)
    {
        return array_key_exists($name, $this->_manyFields);
    }

    /**
     * @return \Mindy\Orm\Fields\ManyToManyField[]
     */
    public function getManyFields()
    {
        return $this->_manyFields;
    }

    /**
     * @return \Mindy\Orm\Fields\HasManyField[]
     */
    public function getHasManyFields()
    {
        return $this->_hasManyFields;
    }

    /**
     * Return initialized fields
     * @return \Mindy\Orm\Fields\Field[]
     */
    public function getFieldsInit()
    {
        return $this->_fields;
    }

    /**
     * Return initialized old fields
     * @return \Mindy\Orm\Fields\Field[]
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
     * @param $name
     * @return bool
     */
    public function hasField($name)
    {
        return isset($this->_fields[$name]);
    }

    /**
     * @param $name
     * @return \Mindy\Orm\Fields\Field|null
     */
    public function getField($name, $throw = true)
    {
        if ($this->hasField($name)) {
            return $this->_fields[$name];
        }

        if ($throw) {
            throw new Exception('Field ' . $name . ' not found');
        } else {
            return null;
        }
    }

    /**
     * Converts the object into an array.
     * @return array the array representation of this object
     */
    public function toArray()
    {
        $data = [];
        foreach($this->getFieldsInit() as $name => $field) {
            if(is_a($field, $this->manyToManyField) || is_a($field, $this->hasManyField)) {
                $data[$name] = $field->getManager()->all();
            } elseif (is_a($field, $this->foreignField) || is_a($field, $this->oneToOneField)) {
                /* @var $model null|Model */
                $model = $field->getValue();
                $modelClass = $field->modelClass;
                $data[$name . '_' . $modelClass::primaryKey()] = $model ? $model->pk : $model;
            } else {
                $data[$name] = $field->getValue();
            }
        }
        return $data;
    }

    public function toJson()
    {
        return Json::encode($this->toArray());
    }
}
