<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
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
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->hasAttribute($name);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * @param $name
     *
     * @return string|int|null
     */
    public function getAttribute($name)
    {
        if (isset($this->mapping[$name])) {
            $name = $this->mapping[$name];
        }

        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
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
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function getOldAttribute($name)
    {
        return isset($this->oldAttributes[$name]) ? $this->oldAttributes[$name] : null;
    }

    /**
     * @return array
     */
    public function getOldAttributes()
    {
        return $this->oldAttributes;
    }

    /**
     * Clear old attributes.
     */
    public function resetOldAttributes()
    {
        $this->oldAttributes = [];
    }

    /**
     * @return array
     */
    public function getDirtyAttributes()
    {
        return array_keys($this->getOldAttributes());
    }

    /**
     * @param string $name
     */
    public function remove($name)
    {
        $this->setAttribute($name, null);
    }
}
