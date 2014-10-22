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
 * @date 03/01/14.01.2014 21:58
 */

namespace Mindy\Orm\Fields;

use Closure;
use Mindy\Helper\Creator;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;
use Mindy\Orm\Model;
use Mindy\Query\ConnectionManager;
use Mindy\Validation\Interfaces\IValidateField;
use Mindy\Validation\RequiredValidator;
use Mindy\Validation\Traits\ValidateField;
use Mindy\Validation\UniqueValidator;

abstract class Field implements IValidateField
{
    use Accessors, Configurator, ValidateField;

    public $verboseName = '';

    public $null = false;

    public $default = null;

    public $length = 0;

    public $required;

    public $value;

    public $editable = true;

    public $choices = [];

    public $helpText;

    public $unique = false;

    public $primary = false;

    protected $name;

    protected $ownerClassName;

    private $_validatorClass = '\Mindy\Validation\Validator';

    private $_extraFields = [];

    private $_model;

    /**
     * @return Field[]
     */
    public function getExtraFieldsInit()
    {
        return $this->_extraFields;
    }

    public function getExtraField($name)
    {
        return $this->_extraFields[$name];
    }

    public function hasExtraField($name)
    {
        return array_key_exists($name, $this->_extraFields);
    }

    public function setExtraField($name, Field $field)
    {
        $this->_extraFields[$name] = $field;
        return $this;
    }

    public function __construct(array $config = [])
    {
        $this->configure($config);

        if ($this->required) {
            $this->validators = array_merge([new RequiredValidator], $this->validators);
        }

        if ($this->unique) {
            $this->validators = array_merge([new UniqueValidator($this->name)], $this->validators);
        }

        $this->init();
    }

    public function init()
    {

    }

    public function setModel(Model $model)
    {
        $this->_model = $model;
        foreach ($this->validators as $validator) {
            if (is_subclass_of($validator, $this->_validatorClass)) {
                $validator->setModel($model);
            }
        }
        return $this;
    }

    public function setModelClass($className)
    {
        $this->ownerClassName = $className;
        foreach ($this->validators as $validator) {
            if (is_subclass_of($validator, $this->_validatorClass)) {
                $validator->setModel($className);
            }
        }
        return $this;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->_model;
    }

    public function getValue()
    {
        return $this->value === null ? $this->default : $this->value;
    }

    public function getDbPrepValue()
    {
        return $this->getValue();
    }

    public function setValue($value)
    {
        return $this->value = $value;
    }

    public function getOptions()
    {
        return [
            'null' => $this->null,
            'default' => $this->default,
            'length' => $this->length,
            'required' => $this->required
        ];
    }

    public function hash()
    {
        return md5(serialize($this->getOptions()));
    }

    public function sql()
    {
        return trim(sprintf('%s %s %s', $this->sqlType(), $this->sqlNullable(), $this->sqlDefault()));
    }

    public function getFormValue()
    {
        return $this->getValue();
    }

    public function isRequired()
    {
        return $this->required === true;
    }

    public function sqlDefault()
    {
        $queryBuilder = ConnectionManager::getDb()->getQueryBuilder();
        $default = $queryBuilder->convertToBoolean($this->default);
        return $this->default === null ? '' : "DEFAULT {$default}";
    }

    public function sqlNullable()
    {
        return $this->null ? 'NULL' : 'NOT NULL';
    }

    public function setName($name)
    {
        $this->name = $name;
        foreach ($this->validators as $validator) {
            if (is_subclass_of($validator, $this->_validatorClass)) {
                $validator->setName($name);
            }
        }
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVerboseName(Model $model)
    {
        if ($this->verboseName) {
            return $this->verboseName;
        } else {
            $name = str_replace('_', ' ', ucfirst($this->name));
            if (method_exists($model, 'getModule')) {
                return $model->getModule()->t($name);
            } else {
                return $name;
            }
        }
    }

    public function getExtraFields()
    {
        return [];
    }

    public function onAfterInsert()
    {

    }

    public function onAfterUpdate()
    {

    }

    public function onAfterDelete()
    {

    }

    public function onBeforeInsert()
    {

    }

    public function onBeforeUpdate()
    {

    }

    public function onBeforeDelete()
    {

    }

    public function getFormField($form, $fieldClass = null)
    {
        if ($this->primary || $this->editable === false) {
            return null;
        }

        if ($fieldClass === null) {
            $fieldClass = $this->choices ? \Mindy\Form\Fields\DropDownField::className() : \Mindy\Form\Fields\CharField::className();
        } elseif ($fieldClass === false) {
            return null;
        }

        $validators = [];
        if ($form->hasField($this->name)) {
            $field = $form->getField($this->name);
            $validators = $field->validators;
        }

        return Creator::createObject([
            'class' => $fieldClass,
            'required' => $this->required || !$this->null,
            'form' => $form,
            'choices' => $this->choices,
            'name' => $this->name,
            'label' => $this->verboseName,
            'hint' => $this->helpText,
            'validators' => array_merge($validators, $this->validators)

//            'html' => [
//                'multiple' => $this->value instanceof RelatedManager
//            ]
        ]);
    }

    abstract public function sqlType();
}
