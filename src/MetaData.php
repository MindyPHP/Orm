<?php

declare(strict_types = 1);

namespace Mindy\Orm;

use Mindy\Creator\Creator;
use Mindy\Orm\Fields\AutoField;
use Mindy\Orm\Fields\Field;
use Mindy\Orm\Fields\FileField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\ModelFieldInterface;
use Mindy\Orm\Fields\OneToOneField;
use Mindy\Orm\Fields\RelatedField;
use ReflectionClass;

/**
 * Class MetaData
 * @package Mindy\Orm
 */
class MetaData
{
    /**
     * Default pk name
     */
    const DEFAULT_PRIMARY_KEY_NAME = 'id';

    /**
     * @var MetaData[]
     */
    private static $instances = [];
    /**
     * @var array
     */
    protected $fields = [];
    /**
     * @var array
     */
    protected $mapping = [];
    /**
     * @var array
     */
    protected $attributes = null;
    /**
     * @var array
     */
    protected $primaryKeys = null;

    /**
     * MetaData constructor.
     * @param string $className
     */
    final private function __construct(string $className)
    {
        $this->init($className);
    }

    /**
     * @param $config
     * @return ModelFieldInterface
     */
    private function createField($config) : ModelFieldInterface
    {
        if (is_string($config)) {
            $config = ['class' => $config];
        }

        if (is_array($config)) {
            $field = Creator::createObject($config);
        } else {
            $field = $config;
        }

        return $field;
    }

    /**
     * @param string $className
     */
    private function init(string $className)
    {
        $primaryFields = [];

        foreach (call_user_func([$className, 'getFields']) as $name => $config) {

            $field = $this->createField($config);
            $field->setName($name);
            $field->setModelClass($className);

            $this->fields[$name] = $field;
            $this->mapping[$field->getAttributeName()] = $name;

            if ($field->primary) {
                $primaryFields[] = $field->getAttributeName();
            }
        }

        if (empty($primaryFields)) {
            $autoField = new AutoField([
                'name' => self::DEFAULT_PRIMARY_KEY_NAME,
                'modelClass' => $className
            ]);

            $this->fields[self::DEFAULT_PRIMARY_KEY_NAME] = $autoField;
            $primaryFields[] = self::DEFAULT_PRIMARY_KEY_NAME;
        }

        $this->primaryKeys = $primaryFields;
    }

    /**
     * @param $subClass
     * @return array|[]ModelFieldInterface
     */
    private function fetchFields($subClass) : array
    {
        $fields = [];
        foreach ($this->fields as $name => $field) {
            if ($field instanceof $subClass) {
                $fields[$name] = $field;
            }
        }
        return $fields;
    }

    /**
     * @return array|[]ModelFieldInterface
     */
    public function getOneToOneFields() : array
    {
        return $this->fetchFields(OneToOneField::class);
    }

    /**
     * @return array|[]ModelFieldInterface
     */
    public function getHasManyFields() : array
    {
        return $this->fetchFields(HasManyField::class);
    }

    /**
     * @return array|[]ModelFieldInterface
     */
    public function getManyToManyFields() : array
    {
        return $this->fetchFields(ManyToManyField::class);
    }

    /**
     * @return array|[]ModelFieldInterface
     */
    public function getForeignFields() : array
    {
        return $this->fetchFields(ForeignField::class);
    }

    /**
     * @deprecated since 3.0
     * @codeCoverageIgnore
     * @return string
     */
    public function getPkName() : string
    {
        return implode('_', $this->primaryKeys);
    }

    /**
     * @param bool $asArray
     * @return array|string
     */
    public function getPrimaryKeyName($asArray = false)
    {
        return $asArray ? $this->primaryKeys : implode('_', $this->primaryKeys);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasRelatedField($name) : bool
    {
        return $this->getField($name) instanceof RelatedField;
    }

    /**
     * @param $name
     * @return bool
     */
    public function getRelatedField($name)
    {
        $field = $this->getField($name);
        return $field instanceof RelatedField ? $field : null;
    }

    /**
     * @return array|[]ModelFieldInterface
     */
    public function getRelatedFields() : array
    {
        return $this->fetchFields(RelatedField::class);
    }

    /**
     * @deprecated since 3.0
     * @codeCoverageIgnore
     * @param $name
     * @return bool
     */
    public function hasForeignKey($name) : bool
    {
        return $this->getField($name) instanceof ForeignField;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasHasManyField($name) : bool
    {
        return array_key_exists($name, $this->getHasManyFields());
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasManyToManyField($name) : bool
    {
        return array_key_exists($name, $this->getManyToManyFields());
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasOneToOneField($name) : bool
    {
        return array_key_exists($name, $this->getOneToOneFields());
    }

    /**
     * @deprecated since 3.0
     * @codeCoverageIgnore
     * @param $name
     * @return mixed|null
     */
    public function getForeignKey($name)
    {
        return $this->getForeignFields()[$name] ?? null;
    }

    /**
     * @param $className
     * @return MetaData
     */
    public static function getInstance($className)
    {
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new self($className);
        }
        return self::$instances[$className];
    }

    /**
     * @return array
     */
    public function getAttributes() : array
    {
        if ($this->attributes === null) {
            /** @var \Mindy\Orm\Model $className */
            $attributes = [];
            foreach ($this->getFields() as $name => $field) {
                $attributeName = $field->getAttributeName();
                if ($attributeName) {
                    $attributes[] = $attributeName;
                }
            }
            $this->attributes = $attributes;
        }
        return $this->attributes;
    }

    /**
     * @deprecated since 3.0
     * @codeCoverageIgnore
     * @return array|[]ModelFieldInterface
     */
    public function getFieldsInit() : array
    {
        return $this->getFields();
    }

    /**
     * @return array|\Mindy\Orm\Fields\ModelFieldInterface[]
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getMappingName($name)
    {
        return $this->mapping[$name] ?? $name;
    }

    /**
     * @param $name
     * @return \Mindy\Orm\Fields\Field
     */
    public function getField($name)
    {
        if ($name === 'pk') {
            $name = $this->getPrimaryKeyName();
        }

        $name = $this->getMappingName($name);

        if (isset($this->fields[$name])) {
            $field = $this->fields[$name];
            $field->cleanValue();
            return $field;
        }

        return null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasField($name)
    {
        if ($name === 'pk') {
            $name = $this->getPkName();
        }
        return array_key_exists($name, $this->fields) || array_key_exists($name, $this->mapping);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasForeignField($name) : bool
    {
        return $this->getField($name) instanceof ForeignField;
    }

    /**
     * @param $name
     * @return ModelFieldInterface
     */
    public function getForeignField($name) : ModelFieldInterface
    {
        $field = $this->getField($name);
        return $field instanceof ForeignField ? $field : null;
    }

    /**
     * @param $name
     * @return ModelFieldInterface
     */
    public function getOneToOneField($name) : ModelFieldInterface
    {
        $field = $this->getField($name);
        return $field instanceof OneToOneField ? $field : null;
    }

    /**
     * @deprecated since 3.0
     * @codeCoverageIgnore
     * @return array|ManyToManyField[]
     */
    public function getManyFields()
    {
        return $this->getManyToManyFields();
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getManyToManyField($name)
    {
        $field = $this->getField($name);
        return $field instanceof ManyToManyField ? $field : null;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getHasManyField($name)
    {
        $field = $this->getField($name);
        return $field instanceof HasManyField ? $field : null;
    }

    /**
     * @param $keys
     * @return bool
     */
    public static function isPrimaryKey($keys)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        $pks = static::getPrimaryKeyName(true);
        if (count($keys) === count($pks)) {
            return count(array_intersect($keys, $pks)) === count($pks);
        } else {
            return false;
        }
    }
}
