<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 10:57
 */

namespace Mindy\Orm;

class AttributeCollection
{
    /**
     * @var array
     */
    protected $attributes = [];
    /**
     * @var array
     */
    protected $oldAttributes = [];

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->hasAttribute($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasAttribute($name) : bool
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * @param $name
     * @return string|int|null
     */
    public function getAttribute($name)
    {
        if (isset($this->mapping[$name])) {
            $name = $this->mapping[$name];
        }
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setAttribute($name, $value)
    {
        $this->oldAttributes[$name] = $this->getAttribute($name);
        $this->attributes[$name] = $value;
    }

    /**
     * @return array
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getOldAttribute($name)
    {
        return $this->oldAttributes[$name] ?? null;
    }

    /**
     * @return array
     */
    public function getOldAttributes() : array
    {
        return $this->oldAttributes;
    }

    /**
     * Clear old attributes
     */
    public function resetOldAttributes()
    {
        $this->oldAttributes = [];
    }

    /**
     * @return array
     */
    public function getDirtyAttributes() : array
    {
        return array_keys($this->getOldAttributes());
    }

    /**
     * @param string $name
     */
    public function remove(string $name)
    {
        $this->setAttribute($name, null);
    }
}