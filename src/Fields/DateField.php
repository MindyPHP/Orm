<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Mindy\Orm\ModelInterface;
use Mindy\QueryBuilder\QueryBuilder;

/**
 * Class DateField
 * @package Mindy\Orm
 */
class DateField extends Field
{
    /**
     * @var bool
     */
    public $autoNowAdd = false;
    /**
     * @var bool
     */
    public $autoNow = false;

    /**
     * @return Type
     */
    public function getSqlType()
    {
        return Type::getType(Type::DATE);
    }

    public function beforeInsert(ModelInterface $model, $value)
    {
        if ($this->autoNowAdd && $model->getIsNewRecord()) {
            $model->setAttribute($this->getAttributeName(), new \DateTime());
        }
    }

    public function beforeUpdate(ModelInterface $model, $value)
    {
        if ($this->autoNow && $model->getIsNewRecord() === false) {
            $model->setAttribute($this->getAttributeName(), new \DateTime());
        }
    }

    public function canBeEmpty()
    {
        return ($this->autoNowAdd || $this->autoNow) || !$this->required && $this->null || !is_null($this->default);
    }

    public function getValue()
    {
        $adapter = QueryBuilder::getInstance($this->getModel()->getConnection())->getAdapter();
        return $adapter->getDate($this->value);
    }

    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\DateField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $this->getSqlType()->convertToPHPValue($value, $platform);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return mixed
     */
    public function convertToDatabaseValueSQL($value, AbstractPlatform $platform)
    {
        if (!is_object($value)) {
            $value = (new \DateTime())->setTimestamp(is_numeric($value) ? $value : strtotime($value));
        }
        return $this->getSqlType()->convertToDatabaseValue($value, $platform);
    }
}
