<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

use ArrayAccess;
use Countable;
use Mindy\Orm\Utils\Collection;
use Mindy\Orm\Utils\CollectionInterface;

class AttributeCollection implements ArrayAccess, Countable
{
    /**
     * @var CollectionInterface
     */
    protected $attributes;
    /**
     * @var CollectionInterface
     */
    protected $oldAttributes;

    /**
     * AttributeCollection constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = new Collection($attributes);
        $this->oldAttributes = new Collection();
    }

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
    public function hasAttribute(string $name): bool
    {
        return $this->attributes->has($name);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAttribute(string $name)
    {
        return $this->attributes->get($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function setAttribute(string $name, $value)
    {
        $this->oldAttributes->set($name, $this->attributes->get($name));
        $this->attributes->set($name, $value);
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes->all();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getOldAttribute(string $name)
    {
        return $this->oldAttributes->get($name);
    }

    /**
     * @return array
     */
    public function getOldAttributes(): array
    {
        return $this->oldAttributes->all();
    }

    /**
     * Clear old attributes.
     */
    public function resetOldAttributes()
    {
        $this->oldAttributes->clear();
    }

    /**
     * @return array
     */
    public function getDirtyAttributes()
    {
        return $this->oldAttributes->keys();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->attributes->offsetExists($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->attributes->offsetGet($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->attributes->offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->attributes->offsetUnset($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->attributes->count();
    }
}
