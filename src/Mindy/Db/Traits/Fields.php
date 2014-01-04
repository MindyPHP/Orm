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

namespace Mindy\Db\Traits;


use Exception;

trait Fields
{
    /**
     * @var string
     */
    public $autoField = '\Mindy\Db\Fields\AutoField';

    /**
     * @var string
     */
    public $relatedField = '\Mindy\Db\Fields\RelatedField';

    /**
     * @var string
     */
    public $foreignField = '\Mindy\Db\Fields\ForeignField';

    /**
     * @var string
     */
    public $manyToManyField = '\Mindy\Db\Fields\ManyToManyField';

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
        foreach ($this->getFields() as $name => $field) {
            /* @var $field \Mindy\Db\Fields\Field */
            if (is_a($field, $this->autoField)) {
                $needPk = false;
            }

            if (is_a($field, $this->relatedField)) {
                /* @var $field \Mindy\Db\Fields\RelatedField */
                if (is_a($field, $this->manyToManyField)) {
                    /* @var $field \Mindy\Db\Fields\ManyToManyField */
                    $field->setModel($this);

                    // @TODO
                    /* @var $newField \Mindy\Db\Fields\ManyToManyField */
//                    $newField = new $this->manyToManyField(static::className());
//                    $newField->setModel($this);
//                    self::$_relations[$field->relatedName] = $newField->getRelation();
//                    $this->_fields[$name] = $newField;

                    $this->_fields[$name] = $field;
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
     * @return \Mindy\Db\Fields\Field|null
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
