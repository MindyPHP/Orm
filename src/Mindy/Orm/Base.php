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
use Mindy\Core\Interfaces\Arrayable;
use ReflectionClass;

class Base implements ArrayAccess, Arrayable
{
    /**
     * @return string the fully qualified name of this class.
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * @return string the short name of this class.
     */
    public static function shortClassName()
    {
        $reflect = new ReflectionClass(self::className());
        return $reflect->getShortName();
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
        return '{{%' . trim(strtolower(preg_replace('/(?<![A-Z])[A-Z]/', '_\0', $class)), '_') . '}}';
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

    /**
     * Converts the object into an array.
     * @return array the array representation of this object
     */
    public function toArray()
    {
        // TODO: Implement toArray() method.
    }
}
