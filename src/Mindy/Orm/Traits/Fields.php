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
 * @date 03/01/14.01.2014 22:50
 */

namespace Mindy\Orm\Traits;


use Exception;

trait Fields
{
    /**
     * @var string
     */
    public $autoField = '\Mindy\Orm\Fields\AutoField';

    /**
     * @var string
     */
    public $relatedField = '\Mindy\Orm\Fields\RelatedField';

    /**
     * @var string
     */
    public $foreignField = '\Mindy\Orm\Fields\ForeignField';

    /**
     * @var string
     */
    public $manyToManyField = '\Mindy\Orm\Fields\ManyToManyField';

    /**
     * @var array
     */
    private $_fields = [];

    /**
     * @var array
     */
    private static $_relations = [];


    /**
     * Initialize fields
     * @void
     */
    public function initFields()
    {
        $needPk = true;

        $m2mFields = [];

        foreach ($this->getFields() as $name => $field) {
            /* @var $field \Mindy\Orm\Fields\Field */
            if (is_a($field, $this->autoField)) {
                $needPk = false;
            }

            if (is_a($field, $this->relatedField)) {
                /* @var $field \Mindy\Orm\Fields\RelatedField */
                if (is_a($field, $this->manyToManyField)) {
                    $m2mFields[$name] = $field;
                } else {
                    $this->_fields[$name] = $field;
                }
            } else {
                $this->_fields[$name] = $field;
            }
        }

        if ($needPk) {
            $this->_fields = array_merge([
                'id' => new $this->autoField()
            ], $this->_fields);
        }

        foreach($m2mFields as $name => $field) {
            /* @var $field \Mindy\Orm\Fields\ManyToManyField */
            $field->setModel($this);

            // @TODO
            /* @var $newField \Mindy\Orm\Fields\ManyToManyField */
//                    $newField = new $this->manyToManyField(static::className());
//                    $newField->setModel($this);
//                    self::$_relations[$field->relatedName] = $newField->getRelation();
//                    $this->_fields[$name] = $newField;

            $this->_fields[$name] = $field;
        }
    }

    /**
     * Return initialized fields
     */
    public function getFieldsInit()
    {
        return $this->_fields;
    }

    /**
     * Example usage:
     * return [
     *     'name' => new CharField(['length' => 250, 'default' => '']),
     *     'email' => new EmailField(),
     * ]
     * @return array
     */
    public function getFields()
    {
        return [];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasField($name)
    {
        return isset($this->_fields[$name]);
    }

    /**
     * @param $name
     * @return \Mindy\Orm\Fields\Field|null
     */
    public function getField($name, $throw = true)
    {
        if($this->hasField($name)) {
            return $this->_fields[$name];
        }

        if($throw) {
            throw new Exception('Field ' . $name . ' not found');
        } else {
            return null;
        }
    }
}
