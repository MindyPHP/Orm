<?php

namespace Mindy\Orm;

use Mindy\Helper\Creator;
use Mindy\Orm\Fields\AutoField;
use Mindy\Orm\Fields\Field;
use Mindy\Orm\Fields\FileField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Fields\OneToOneField;
use Mindy\Orm\Fields\RelatedField;

/**
 * Class MetaData
 * @package Mindy\Orm
 */
class MetaData
{
    /**
     * @var MetaData[]
     */
    private static $instances = [];
    /**
     * @var string
     */
    protected $modelClassName;
    /**
     * @var array
     */
    protected $allFields = [];
    /**
     * @var array
     */
    protected $localFields = [];
    /**
     * @var array
     */
    protected $localFileFields = [];
    /**
     * @var array
     */
    protected $extFields = [];
    /**
     * @var array
     */
    protected $foreignFields = [];
    /**
     * @var array
     */
    protected $oneToOneFields = [];
    /**
     * @var array
     */
    protected $manyToManyFields = [];
    /**
     * @var array
     */
    protected $hasManyFields = [];
    /**
     * @var array
     */
    protected $attributes = null;
    /**
     * @var array
     */
    protected $primaryKeys = null;

    public function __construct($className)
    {
        $this->modelClassName = $className;
    }

    public function getPkName()
    {
        return implode('_', $this->primaryKey());
    }

    public function hasRelatedField($name)
    {
        return $this->hasManyToManyField($name) || $this->hasHasManyField($name) || $this->hasForeignField($name);
    }

    /**
     * @param $name
     * @return \Mindy\Orm\Fields\HasManyField|\Mindy\Orm\Fields\ManyToManyField|\Mindy\Orm\Fields\ForeignField|null
     */
    public function getRelatedField($name)
    {
        if ($this->hasManyToManyField($name)) {
            return $this->getManyToManyField($name);
        } else if ($this->hasHasManyField($name)) {
            return $this->getHasManyField($name);
        } else if ($this->hasForeignField($name)) {
            return $this->getForeignField($name);
        }
        return null;
    }

    /**
     * TODO refactoring
     * @return array
     */
    public function getRelatedFields()
    {
        return array_keys(array_merge(array_merge($this->hasManyFields, $this->manyToManyFields), array_flip($this->foreignFields)));
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasFileField($name)
    {
        return array_key_exists($name, $this->localFileFields);
    }

    /**
     * @param $name
     * @return \Mindy\Orm\Fields\FileField
     */
    public function getFileField($name)
    {
        $field = $this->localFileFields[$name];
        $field->cleanValue();
        return $field;
    }

    public function hasForeignKey($name)
    {
        return array_key_exists($name, $this->foreignFields);
    }

    public function hasHasManyField($name)
    {
        return array_key_exists($name, $this->hasManyFields);
    }

    public function hasManyToManyField($name)
    {
        return array_key_exists($name, $this->manyToManyFields);
    }

    public function hasOneToOneField($name)
    {
        if (array_key_exists($name, $this->oneToOneFields)) {
            $name = $this->oneToOneFields[$name];
        }
        if ($this->hasField($name)) {
            return $this->getField($name) instanceof OneToOneField;
        }
        return false;
    }

    public function getForeignKey($name)
    {
        return $this->foreignFields[$name];
    }

    public function primaryKey()
    {
        if (is_array($this->primaryKeys)) {
            return $this->primaryKeys;
        } else if (is_null($this->primaryKeys)) {
            $this->primaryKeys = [];
            foreach ($this->allFields as $name => $field) {
                if ($field instanceof ManyToManyField || $field instanceof HasManyField) {
                    continue;
                }

                if ($field->primary) {
                    $this->primaryKeys[] = $field instanceof RelatedField ? $name . '_id' : $name;
                }
            }
            return $this->primaryKeys;
        }

        return [];
    }

    /**
     * @param $className
     * @return MetaData
     */
    public static function getInstance($className)
    {
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new self($className);
            self::$instances[$className]->initFields();
        }

        return self::$instances[$className];
    }

    /**
     * @return array
     * @throws \Mindy\Exception\InvalidConfigException
     */
    public function getAttributes()
    {
        if ($this->attributes === null) {
            /** @var \Mindy\Orm\Model $className */
//            $className = $this->modelClassName;
//            $this->attributes = array_keys($className::getTableSchema()->columns);
            $attributes = [];
            foreach ($this->getFieldsInit() as $name => $field) {
                if ($field instanceof ManyToManyField || $field instanceof HasManyField) {
                    continue;
                }

                /** @var $field \Mindy\Orm\Fields\Field */
                if ($field instanceof OneToOneField) {
                    /** @var $field \Mindy\Orm\Fields\OneToOneField */
                    if ($field->reversed) {
                        $attributes[] = $name . '_id';
                    } else {
                        $attributes[] = $name . '_' . $field->getForeignPrimaryKey();
                    }
                } else if ($field instanceof ForeignField) {
                    /** @var $field \Mindy\Orm\Fields\ForeignField */
                    $attributes[] = $name . '_' . $field->getForeignPrimaryKey();
                } else {
                    $attributes[] = $name;
                }
            }
            $this->attributes = $attributes;
        }
        return $this->attributes;
    }

    public function initFields(array $fields = [], $extra = false)
    {
        $className = $this->modelClassName;
        if (empty($fields)) {
            $fields = $className::getFields();
        }

        $needPk = !$extra;
        $fkFields = [];
        $m2mFields = [];

        foreach ($fields as $name => $config) {
            /* @var $field \Mindy\Orm\Fields\Field */

            if (!is_object($config) && !$config instanceof Field) {
                $field = Creator::createObject($config);
                $field->setName($name);
                $field->setModelClass($className);
            } else {
                $field = $config;
            }
            // $field->setModel($model);

            if (is_a($field, AutoField::class) || ($field->primary && ($field instanceof OneToOneField) === false)) {
                $needPk = false;
                $this->primaryKeys[] = $name;
            }

            if (is_a($field, FileField::class)) {
                $this->localFileFields[$name] = $field;
            } else if (is_a($field, RelatedField::class)) {
                /* @var $field \Mindy\Orm\Fields\RelatedField */
                if (is_a($field, ManyToManyField::class)) {
                    /* @var $field \Mindy\Orm\Fields\ManyToManyField */
                    $this->manyToManyFields[$name] = $field;
                    $m2mFields[$name] = $field;
                } else if (is_a($field, HasManyField::class)) {
                    /* @var $field \Mindy\Orm\Fields\HasManyField */
                    $this->hasManyFields[$name] = $field;
                } else if (is_a($field, OneToOneField::class)) {
                    /* @var $field \Mindy\Orm\Fields\OneToOneField */
                    if ($field->reversed) {
                        $this->oneToOneFields[$name . '_id'] = $name;
                    } else {
                        $needPk = false;
                        $this->primaryKeys[] = $name . '_' . $field->getForeignPrimaryKey();
                        $this->oneToOneFields[$name . '_' . $field->getForeignPrimaryKey()] = $name;
                    }
                } elseif (is_a($field, ForeignField::class)) {
                    /* @var $field \Mindy\Orm\Fields\ForeignField */
                    $fkFields[$name] = $field;
                }
            } else {
                $this->localFields[$name] = $field;
            }
            $this->allFields[$name] = $field;

            if (!$extra) {
                $extraFields = $field->getExtraFields();
                if (!empty($extraFields)) {
                    $extraFieldsInitialized = $this->initFields($extraFields, true);
                    foreach ($extraFieldsInitialized as $key => $value) {
                        $field->setExtraField($key, $this->allFields[$key]);
                        $this->extFields[$name] = [$key => $this->allFields[$key]];
                    }
                }
            }
        }

        if ($needPk) {
            $pkName = 'id';
            /* @var $autoField \Mindy\Orm\Fields\AutoField */
            $autoField = new AutoField;
            $autoField->setName($pkName);
            // $autoField->setModel($model);

            $this->allFields = array_merge([$pkName => $autoField], $this->allFields);
            $this->localFields = array_merge([$pkName => $autoField], $this->localFields);
            $this->primaryKeys[] = $pkName;
        }

        foreach ($fkFields as $name => $field) {
            // ForeignKey in self model
            if ($field->modelClass == $className) {
                $this->foreignFields[$name . '_' . $this->getPkName()] = $name;
            } else {
                $this->foreignFields[$name . '_' . $field->getForeignPrimaryKey()] = $name;
            }
        }

        return $fields;
    }

    public function getFieldsInit()
    {
        return $this->allFields;
    }

    /**
     * @param $name
     * @return \Mindy\Orm\Fields\Field
     */
    public function getField($name)
    {
        if ($name === 'pk') {
            $name = $this->getPkName();
        }
        $field = $this->allFields[$name];
        $field->cleanValue();
        return $field;
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
        return array_key_exists($name, $this->allFields);
    }

    public function hasForeignField($name)
    {
        if (array_key_exists($name, $this->foreignFields)) {
            $name = $this->foreignFields[$name];
        }
        if ($this->hasField($name)) {
            return $this->getField($name) instanceof ForeignField;
        }
        return false;
    }

    public function getForeignField($name)
    {
        if (array_key_exists($name, $this->foreignFields)) {
            $name = $this->foreignFields[$name];
        }
        return $this->getField($name);
    }

    public function getOneToOneField($name)
    {
        if (array_key_exists($name, $this->oneToOneFields)) {
            $name = $this->oneToOneFields[$name];
        }
        return $this->getField($name);
    }

    public function getForeignFields()
    {
        return $this->foreignFields;
    }

    public function getOneToOneFields()
    {
        return $this->oneToOneFields;
    }

    public function getManyFields()
    {
        return $this->manyToManyFields;
    }

    public function getManyToManyField($name)
    {
        return $this->manyToManyFields[$name];
    }

    public function getHasManyField($name)
    {
        return $this->manyToManyFields[$name];
    }

    public function hasExtraFields($name)
    {
        return array_key_exists($name, $this->extFields);
    }

    public function getExtraFields($name)
    {
        return $this->extFields[$name];
    }
}
