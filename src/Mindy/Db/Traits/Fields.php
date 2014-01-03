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
     * Initialize fields
     * @void
     */
    public function initFields()
    {
        /* @var $field \Mindy\Db\Fields\Field */
        /* @var $field \Mindy\Db\Fields\RelatedField */

        $needPk = true;
        foreach ($this->getFields() as $name => $field) {
            if (is_a($field, $this->autoField)) {
                $needPk = false;
            }

            if (is_a($field, $this->relatedField)) {
                if (is_a($field, $this->manyToManyField)) {
                    $newField = new $this->manyToManyField([
                        'owner' => $this,
                        'model' => $field->getRelation()->modelClass,
                    ]);
                    $this->_fields[$name] = $newField;
                    self::$_relations[$field->relatedName] = $newField->getRelation();
                } else {
                    $this->_fields[$name . '_id'] = $field;
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
    public function getField($name)
    {
        if($this->hasField($name)) {
            return $this->_fields[$name];
        }

        return null;
    }
}
