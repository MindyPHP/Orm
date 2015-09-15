<?php

namespace Mindy\Orm\Fields;

use Mindy\Helper\Creator;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;
use Mindy\Orm\Base;
use Mindy\Orm\Model;
use Mindy\Validation\Interfaces\IValidateField;
use Mindy\Validation\RequiredValidator;
use Mindy\Validation\Traits\ValidateField;
use Mindy\Validation\UniqueValidator;
use Mindy\Validation\Validator;

/**
 * Class Field
 * @package Mindy\Orm
 */
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

    public $autoFetch = false;

    protected $name;

    protected $ownerClassName;

    private $_validatorClass = '\Mindy\Validation\Validator';

    private $_extraFields = [];
    /**
     * @var \Mindy\Orm\Model
     */
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

    public function getValidators()
    {
        $model = $this->getModel();

        $hasRequired = false;
        $hasUnique = false;
        foreach ($this->validators as $validator) {
            if ($validator instanceof RequiredValidator) {
                $hasRequired = true;
            }
            if ($validator instanceof UniqueValidator) {
                $hasUnique = true;
            }
            if ($validator instanceof Validator) {
                $validator->setName($this->name);
                if ($model) {
                    $validator->setModel($model);
                }
            }
        }

        if ($model) {
            $attribute = $model->getAttribute($this->name);
            if (
                $this->autoFetch === false &&
                $hasRequired === false &&
                !$this->canBeEmpty() &&
                $model->getIsNewRecord() &&
                empty($attribute)
            ) {
                $requiredValidator = new RequiredValidator;
                $requiredValidator->setName($this->name);
                $requiredValidator->setModel($model);
                $this->validators = array_merge([$requiredValidator], $this->validators);
            }
        }

        if ($model && $hasUnique === false && $this->unique) {
            $uniqueValidator = new UniqueValidator();
            $uniqueValidator->setName($this->name);
            $uniqueValidator->setModel($model);
            $this->validators = array_merge([$uniqueValidator], $this->validators);
        }

        return $this->validators;
    }

    public function canBeEmpty()
    {
        return !$this->required && $this->null || !is_null($this->default) || $this->autoFetch === true;
    }

    public function setModel(Base $model)
    {
        $this->_model = $model;
        return $this;
    }

    public function setModelClass($className)
    {
        $this->ownerClassName = $className;
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
        if (empty($this->value)) {
            return $this->null == true ? null : $this->default;
        }
        return  $this->value;
    }

    public function getDbPrepValue()
    {
        return $this->getValue();
    }

    public function cleanValue()
    {
        $this->value = null;
    }

    public function setDbValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function setValue($value)
    {
        return $this->value = $value;
    }

    public function getOptions()
    {
        return [
            'sqlType' => $this->sqlType(),
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
        return $this->default === null ? '' : "DEFAULT {$this->default}";
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
                $validator->setModel($this->getModel());
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

    public function getFormField($form, $fieldClass = null, array $extra = [])
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

        if (($this->null === false || $this->required) && $this->autoFetch === false && ($this instanceof BooleanField) === false) {
            $validator = new RequiredValidator;
            $validator->setName($this->name);
            $validator->setModel($this);
            $validators[] = $validator;
        }

        if ($this->unique) {
            $validator = new UniqueValidator;
            $validator->setName($this->name);
            $validator->setModel($this);
            $validators[] = $validator;
        }

        return Creator::createObject(array_merge([
            'class' => $fieldClass,
            'required' => !$this->canBeEmpty(),
            'form' => $form,
            'choices' => $this->choices,
            'name' => $this->name,
            'label' => $this->verboseName,
            'hint' => $this->helpText,
            'validators' => array_merge($validators, $this->validators),
            'value' => $this->default ? $this->default : null

//            'html' => [
//                'multiple' => $this->value instanceof RelatedManager
//            ]
        ], $extra));
    }

    public function toArray()
    {
        return $this->getValue();
    }

    public function toText()
    {
        $value = $this->getValue();
        if (isset($this->choices[$value])) {
            $value = $this->choices[$value];
        }
        return $value;
    }

    public function hasChoices()
    {
        return !empty($this->choices);
    }

    abstract public function sqlType();
}
