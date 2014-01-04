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

namespace Mindy\Db;


use Exception;

class Orm extends Base
{
    /**
     * @var array validation errors (attribute name => array of errors)
     */
    private $_errors = [];

    public function __construct()
    {
        $this->initFields();
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
            $this->getField($name)->setValue($value);
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
        if($name == 'pk') {
            return $this->getPk();
        }

        if ($this->hasField($name)) {
            $field = $this->getField($name);
            if (is_a($field, $this->relatedField)) {
                if (is_a($field, $this->foreignField)) {
                    return $field->getValue();
                } else if (is_a($field, $this->manyToManyField)) {
                    /* @var $field \Mindy\Db\Fields\ManyToManyField */
                    return $field->getRelation();
                } else {
                    throw new Exception("Unknown field type " . $name . " in " . get_class($this));
                }
            } else {
                return $field->getValue();
            }
        }

        throw new Exception('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    public function getPk()
    {
        /* @var $field \Mindy\Db\Fields\Field */
        if($this->hasField('id')) {
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

        /* @var $field \Mindy\Db\Fields\Field */
        foreach ($this->getFieldsInit() as $name => $field) {
            if ($field->isValid() === false) {
                foreach ($field->getErrors() as $error) {
                    $this->addError($name, $error);
                }
            }
        }

        return $this->hasErrors() === false;
    }
}
