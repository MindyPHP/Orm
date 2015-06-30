<?php

namespace Mindy\Orm;

use Mindy\Helper\Creator;
use Mindy\Orm\Fields\ForeignField;

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
     * @var \Mindy\Orm\Fields\Field
     */
    protected $primaryKeyField;
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
        return $this->primaryKeyField->getName();
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
        return array_key_exists($name, $this->oneToOneFields);
    }

    public function getForeignKey($name)
    {
        return $this->foreignFields[$name];
    }

    public function primaryKey()
    {
        if (is_null($this->primaryKeys)) {
            $this->primaryKeys = [];
            foreach ($this->allFields as $name => $field) {
                if ($field->primary) {
                    if ($this->hasForeignField($name)) {
                        $this->primaryKeys[] = $name . '_id';
                    } else {
                        $this->primaryKeys[] = $name;
                    }
                }
            }
        }
        return $this->primaryKeys;
    }

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
            $className = $this->modelClassName;
            $this->attributes = array_keys($className::getTableSchema()->columns);
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

            if (!is_object($config) && !$config instanceof \Mindy\Orm\Fields\Field) {
                $field = Creator::createObject($config);
                $field->setName($name);
                $field->setModelClass($className);
            } else {
                $field = $config;
            }
            // $field->setModel($model);

            if (is_a($field, $className::$autoField) || $field->primary) {
                $needPk = false;
                $this->primaryKeyField = $field;
            }

            if (is_a($field, $className::$fileField)) {
                $this->localFileFields[$name] = $field;
            }

            if (is_a($field, $className::$relatedField)) {
                /* @var $field \Mindy\Orm\Fields\RelatedField */
                if (is_a($field, $className::$manyToManyField)) {
                    /* @var $field \Mindy\Orm\Fields\ManyToManyField */
                    $this->manyToManyFields[$name] = $field;
                    $m2mFields[$name] = $field;
                }

                if (is_a($field, $className::$hasManyField)) {
                    /* @var $field \Mindy\Orm\Fields\HasManyField */
                    $this->hasManyFields[$name] = $field;
                }

                if (is_a($field, $className::$oneToOneField) && $field->reversed) {
                    /* @var $field \Mindy\Orm\Fields\ForeignField */
                    $this->oneToOneFields[$name] = $field;
                }elseif (is_a($field, $className::$foreignField)) {
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
            $autoFieldClass = $className::$autoField;
            $autoField = new $autoFieldClass;
            $autoField->setName($pkName);
            // $autoField->setModel($model);

            $this->allFields = array_merge([$pkName => $autoField], $this->allFields);
            $this->localFields = array_merge([$pkName => $autoField], $this->localFields);
            $this->primaryKeyField = $autoField;
        }

        foreach ($fkFields as $name => $field) {
            // ForeignKey in self model
            if ($field->modelClass == $className) {
                $this->foreignFields[$name . '_' . $this->primaryKeyField->getName()] = $name;
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

    public function getForeignFields()
    {
        return $this->foreignFields;
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
