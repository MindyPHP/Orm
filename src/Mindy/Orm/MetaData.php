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

class MetaData
{
    /**
     * @var MetaData[]
     */
    private static $instances = [];

    private static $manyFields = [];

    private static $fields = [];

    private static $extrafields = [];

    public static $fkFields = [];

    private static $hasManyFields = [];

    private static $fileFields = [];

    public static $pkName = [];

    public function __construct($className)
    {
        self::$manyFields[$className] = [];
        self::$hasManyFields[$className] = [];
        self::$fields[$className] = [];
        self::$extrafields[$className] = [];
        self::$fileFields[$className] = [];

//        foreach(self::$fields[$className] as $name => $field) {
//            if(array_key_exists($name, self::$hasManyFields[$className]) || array_key_exists($name, self::$manyFields[$className])) {
//                continue;
//            }
//            $model->setAttribute($name, $field->default);
//        }
    }

    public function getPkName($className)
    {
        return self::$pkName[$className];
    }

    /**
     * @param $className
     * @param $name
     * @return bool
     */
    public function hasFileField($className, $name)
    {
        return array_key_exists($name, self::$fileFields[$className]);
    }

    /**
     * @param $className
     * @param $name
     * @return \Mindy\Orm\Fields\FileField
     */
    public function getFileField($className, $name)
    {
        return self::$fileFields[$className][$name];
    }

    public function hasForeignKey($className, $name)
    {
        return array_key_exists($name, self::$fkFields[$className]);
    }

    public function hasHasManyField($className, $name)
    {
        return array_key_exists($name, self::$hasManyFields[$className]);
    }

    public function hasManyToManyField($className, $name)
    {
        return array_key_exists($name, self::$manyFields[$className]);
    }

    public function getForeignKey($className, $name)
    {
        return $this->getField($className, self::$fkFields[$className][$name]);
    }

    public function primaryKey($className)
    {
        $primaryKeys = [];
        foreach(self::$fields[$className] as $name => $field) {
            if($field->primary) {
                $primaryKeys[] = $name;
            }
        }
        return $primaryKeys;
    }

    public static function getInstance($className)
    {
        if(!isset(self::$instances[$className])) {
            self::$instances[$className] = new self($className);
            self::$instances[$className]->initFields($className);
        }

        return self::$instances[$className];
    }

    public function initFields($className, array $fields = [], $extra = false)
    {
        if (empty($fields)) {
            $fields = $className::getFields();
        }

        $needPk = !$extra;
        $fkFields = [];

        foreach ($fields as $name => $config) {
            /* @var $field \Mindy\Orm\Fields\Field */

            $field = Creator::createObject($config);
            $field->setName($name);
            $field->setModelClass($className);
            // $field->setModel($model);

            if (is_a($field, $className::$autoField) || $field->primary) {
                $needPk = false;
                self::$pkName[$className] = $name;
            }

            if(is_a($field, $className::$fileField)) {
                self::$fileFields[$className][$name] = $field;
            }

            if (is_a($field, $className::$relatedField)) {
                /* @var $field \Mindy\Orm\Fields\RelatedField */
                if (is_a($field, $className::$manyToManyField)) {
                    /* @var $field \Mindy\Orm\Fields\ManyToManyField */
                    self::$manyFields[$className][$name] = $field;
                }

                if (is_a($field, $className::$hasManyField)) {
                    /* @var $field \Mindy\Orm\Fields\HasManyField */
                    self::$hasManyFields[$className][$name] = $field;
                }

                if (is_a($field, $className::$foreignField)) {
                    /* @var $field \Mindy\Orm\Fields\ForeignField */
                    self::$fields[$className][$name] = $field;
                    $fkFields[$name] = $field;
                }
            } else {
                self::$fields[$className][$name] = $field;
            }

            if (!$extra) {
                $extraFields = $field->getExtraFields();
                if (!empty($extraFields)) {
                    $extraFieldsInitialized = self::initFields($className, $extraFields, true);
                    foreach ($extraFieldsInitialized as $key => $value) {
                        $field->setExtraField($key, self::$fields[$className][$key]);
                        self::$extrafields[$className][$name] = [
                            $key => self::$fields[$className][$key]
                        ];
                    }
                }
            }
        }

        if ($needPk) {
            self::$pkName[$className] = $name = 'id';

            /* @var $autoField \Mindy\Orm\Fields\AutoField */
            $autoFieldClass = $className::$autoField;
            $autoField = new $autoFieldClass;
            $autoField->setName($name);
            // $autoField->setModel($model);

            self::$fields[$className] = array_merge([$name => $autoField], self::$fields[$className]);
        }

        foreach($fkFields as $name => $field) {
            // ForeignKey in self model
            if ($field->modelClass == $className) {
                self::$fkFields[$className][$name . '_' . self::$pkName[$className]] = $name;
            } else {
                self::$fkFields[$className][$name . '_' . $field->getForeignPrimaryKey()] = $name;
            }
        }

        foreach (self::$manyFields[$className] as $name => $field) {
            /* @var $field \Mindy\Orm\Fields\ManyToManyField */
            self::$fields[$className][$name] = $field;
        }

        foreach (self::$hasManyFields[$className] as $name => $field) {
            /* @var $field \Mindy\Orm\Fields\HasManyField */
            self::$fields[$className][$name] = $field;
        }

        return $fields;
    }

    public function getFieldsInit($className)
    {
        return self::$fields[$className];
    }

    /**
     * @param $className
     * @param $name
     * @return \Mindy\Orm\Fields\Field
     */
    public function getField($className, $name)
    {
        return self::$fields[$className][$name];
    }

    /**
     * @param $className
     * @param $name
     * @return bool
     */
    public function hasField($className, $name)
    {
        return array_key_exists($name, self::$fields[$className]);
    }

    public function hasForeignField($className, $name)
    {
        if(array_key_exists($className, self::$fkFields) && array_key_exists($name, self::$fkFields[$className])) {
            $name = self::$fkFields[$className][$name];
        }
        if($this->hasField($className, $name)) {
            return $this->getField($className, $name) instanceof ForeignField;
        }
        return false;
    }

    public function getForeignField($className, $name)
    {
        if(array_key_exists($className, self::$fkFields) && array_key_exists($name, self::$fkFields[$className])) {
            $name = self::$fkFields[$className][$name];
        }
        return $this->getField($className, $name);
    }

    public function getManyFields($className)
    {
        return self::$manyFields[$className];
    }

    public function hasExtraFields($className, $name)
    {
        return array_key_exists($name, self::$extrafields[$className]);
    }

    public function getExtraFields($className, $name)
    {
        return self::$extrafields[$className][$name];
    }
}
