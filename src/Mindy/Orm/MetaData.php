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
 * @date 21/05/14.05.2014 14:45
 */

namespace Mindy\Orm;


use Mindy\Helper\Creator;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Fields\ManyToManyField;

class MetaData
{
    /**
     * @var MetaData[]
     */
    private static $instances = [];
    /**
     * @var array
     */
    public $backwardFields = [];
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
    protected $manyToManyFields = [];
    /**
     * @var array
     */
    protected $hasManyFields = [];

    public function __construct($className)
    {
        $this->modelClassName = $className;
    }

    public function getPkName()
    {
        return $this->primaryKeyField->getName();
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
        return $this->localFileFields[$name];
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

    public function getForeignKey($name)
    {
        return $this->foreignFields[$name];
    }

    public function primaryKey()
    {
        $primaryKeys = [];
        foreach ($this->allFields as $name => $field) {
            if ($field->primary) {
                $primaryKeys[] = $name;
            }
        }
        return $primaryKeys;
    }

    public static function getInstance($className)
    {
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new self($className);
            self::$instances[$className]->initFields();
        }

        return self::$instances[$className];
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

                if (is_a($field, $className::$foreignField)) {
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

        foreach($fkFields as $name => $field) {
            // ForeignKey in self model
            if ($field->modelClass == $className) {
                $this->foreignFields[$name . '_' . $this->primaryKeyField->getName()] = $name;
            } else {
                $this->foreignFields[$name . '_' . $field->getForeignPrimaryKey()] = $name;
            }
        }

        if(!$extra) {
            foreach ($m2mFields as $name => $field) {
                $this->backwardFields[] = $name;
                $targetClass = $field->modelClass;
                $metaInstance = $this->getInstance($targetClass);
                $relatedName = $field->getRelatedName();

                $m2mField = new ManyToManyField([
                    'name' => $relatedName,
                    'modelClass' => $className,
                ]);
                $m2mField->setModelClass($field->modelClass);

                $metaInstance->initFields([$relatedName => $m2mField], true);
            }
        }

        foreach ($fkFields as $name => $field) {
            $this->backwardFields[] = $name;
            $targetClass = $field->modelClass;
            $metaInstance = $this->getInstance($targetClass);
            $relatedName = $field->getRelatedName();

            $hasManyField = new HasManyField([
                'name' => $relatedName,
                'modelClass' => $className,
                'to' => $name . '_' . $targetClass::getPkName()
            ]);
            $hasManyField->setModelClass($field->modelClass);

            $metaInstance->initFields([$relatedName => $hasManyField], true);
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
        return $this->allFields[$name];
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
        if(array_key_exists($name, $this->foreignFields)) {
            $name = $this->foreignFields[$name];
        }
        if($this->hasField($name)) {
            return $this->getField($name) instanceof ForeignField;
        }
        return false;
    }

    public function getForeignField($name)
    {
        if(array_key_exists($name, $this->foreignFields)) {
            $name = $this->foreignFields[$name];
        }
        return $this->getField($name);
    }

    public function getManyFields()
    {
        return $this->manyToManyFields;
    }

    public function hasExtraFields($name)
    {
        return array_key_exists($name, $this->extFields);
    }

    public function getExtraFields($name)
    {
        return $this->extFields[$name];
    }

    public function isBackwardField($name)
    {
        return in_array($name, $this->backwardFields);
    }
}
