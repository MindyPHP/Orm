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
use Mindy\Orm\Model;
use Mindy\Orm\Validator\RequiredValidator;

abstract class Field
{
    public $verboseName = '';

    public $null = false;

    public $default = null;

    public $length = 0;

    public $required;

    public $value;

    public $editable = true;

    public $choices = [];

    public $validators = [];

    protected $name;

    private $_validatorClass = '\Mindy\Orm\Validator\Validator';

    private $_errors = [];

    private $_extraFields = [];

    private $_model;

    public $primary = false;

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

    /**
     * @return string the fully qualified name of this class.
     */
    public static function className()
    {
        return get_called_class();
    }

    public function __construct(array $config = [])
    {
        Creator::configure($this, $config);

        if ($this->required) {
            $this->validators = array_merge([new RequiredValidator], $this->validators);
        }

        $this->init();
    }

    public function init()
    {

    }

    public function clearErrors()
    {
        $this->_errors = [];
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function hasErrors()
    {
        return !empty($this->_errors);
    }

    public function addErrors($errors)
    {
        $this->_errors = array_merge($this->_errors, $errors);
    }

    public function setModel(Model $model)
    {
        $this->_model = $model;
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

    public function isRequired()
    {
        return $this->required === true;
    }

    public function sqlDefault()
    {
        $default = $this->default ? 'TRUE' : 'FALSE';
        return $this->default === null ? '' : "DEFAULT {$default}";
    }

    public function sqlNullable()
    {
        return $this->null ? 'NULL' : 'NOT NULL';
    }

    public function isValid()
    {
        $this->clearErrors();
        foreach ($this->validators as $validator) {
            if ($validator instanceof Closure) {
                /* @var $validator \Closure */
                $valid = $validator->__invoke($this->value);
                if ($valid !== true) {
                    if (!is_array($valid)) {
                        $valid = [$valid];
                    }

                    $this->addErrors($valid);
                }
            } else if (is_subclass_of($validator, $this->_validatorClass)) {
                /* @var $validator \Mindy\Orm\Validator\Validator */
                $validator->clearErrors();

                $valid = $validator->validate($this->value);
                if ($valid === false) {
                    $this->addErrors($validator->getErrors());
                }
            }
        }

        return $this->hasErrors() === false;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
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

    abstract public function sqlType();
}
