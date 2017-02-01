<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Fields;

use Closure;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Types\Type;
use Mindy\Orm\Model;
use Mindy\Orm\ModelInterface;
use Mindy\Orm\ValidationTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class Field.
 */
abstract class Field implements ModelFieldInterface
{
    use ValidationTrait;

    /**
     * @var string|null|false
     */
    public $comment;
    /**
     * @var bool
     */
    public $null = false;
    /**
     * @var null|string|int
     */
    public $default = null;
    /**
     * @var int|string
     */
    public $length = 0;

    public $verboseName = '';

    public $editable = true;

    public $choices = [];

    public $helpText;

    public $unique = false;

    public $primary = false;

    public $autoFetch = false;

    protected $name;

    protected $ownerClassName;

    /**
     * @var \Mindy\Orm\Model
     */
    private $_model;

    /**
     * @var array
     */
    protected $validators = [];
    /**
     * @var mixed
     */
    protected $value;
    /**
     * @var mixed
     */
    protected $dbValue;

    /**
     * Field constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @return array
     */
    public function getValidationConstraints()
    {
        $constraints = [];
        if ($this->isRequired()) {
            $constraints[] = new Assert\NotBlank();
        }

        if ($this->unique) {
            $constraints[] = new Assert\Callback(function ($value, ExecutionContextInterface $context, $payload) {
                if ($value === null && $this->null === true) {
                    return;
                }

                if ($this->getModel()->objects()->filter(['pk' => $value])->count() > 0) {
                    $context->buildViolation('The value must be unique')->addViolation();
                }
            });
        }

        if (!empty($this->choices)) {
            $constraints[] = new Assert\Choice([
                'choices' => array_keys($this->choices instanceof Closure ? $this->choices->__invoke() : $this->choices),
            ]);
        }

        return array_merge($constraints, $this->validators);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return Column
     */
    public function getColumn()
    {
        $type = $this->getSqlType();
        if ($type) {
            return new Column($this->getAttributeName(), $type, $this->getSqlOptions());
        }
    }

    /**
     * @return array
     */
    public function getSqlIndexes()
    {
        $indexes = [];
        if ($this->unique && $this->primary === false) {
            $indexes[] = new Index($this->name.'_idx', [$this->name], true, false);
        }

        return $indexes;
    }

    /**
     * @return array
     */
    public function getSqlOptions()
    {
        $options = [];

        foreach (['length', 'default', 'comment'] as $key) {
            if ($this->{$key} !== null) {
                $options[$key] = $this->{$key};
            }
        }

        if ($this->null) {
            $options['notnull'] = false;
        }

        return $options;
    }

    /**
     * @return string|bool
     */
    public function getAttributeName()
    {
        return $this->name;
    }

    /**
     * @return Type
     */
    abstract public function getSqlType();

    /**
     * @param ModelInterface $model
     *
     * @return $this
     */
    public function setModel(ModelInterface $model)
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

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->setDbValue($value);
    }

    /**
     * @return int|mixed|null|string
     */
    public function getValue()
    {
        if (empty($this->value)) {
            return $this->null === true ? null : $this->default;
        }

        return $this->value;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setDbValue($value)
    {
        $this->dbValue = $value;

        return $this;
    }

    public function cleanValue()
    {
        $this->value = null;
    }

    public function getFormValue()
    {
        return $this->getValue();
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->null === false && is_null($this->default) === true;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVerboseName()
    {
        if ($this->verboseName) {
            return $this->verboseName;
        }

        return str_replace('_', ' ', ucfirst($this->name));
    }

    /**
     * @param ModelInterface $model
     * @param $value
     */
    public function afterInsert(ModelInterface $model, $value)
    {
    }

    /**
     * @param ModelInterface $model
     * @param $value
     */
    public function afterUpdate(ModelInterface $model, $value)
    {
    }

    /**
     * @param ModelInterface $model
     * @param $value
     */
    public function afterDelete(ModelInterface $model, $value)
    {
    }

    /**
     * @param ModelInterface $model
     * @param $value
     */
    public function beforeInsert(ModelInterface $model, $value)
    {
    }

    /**
     * @param ModelInterface $model
     * @param $value
     */
    public function beforeUpdate(ModelInterface $model, $value)
    {
    }

    /**
     * @param ModelInterface $model
     * @param $value
     */
    public function beforeDelete(ModelInterface $model, $value)
    {
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

    /**
     * @param $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $this->getSqlType()->convertToPHPValue($value, $platform);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $this->getSqlType()->convertToDatabaseValue($value, $platform);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     */
    public function convertToPHPValueSQL($value, AbstractPlatform $platform)
    {
        return $this->getSqlType()->convertToPHPValueSQL($value, $platform);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     */
    public function convertToDatabaseValueSQL($value, AbstractPlatform $platform)
    {
        if ($value === null || $value === '') {
            $value = null;
        }

        return $this->getSqlType()->convertToDatabaseValueSQL($value, $platform);
    }
}
