<?php

namespace Mindy\Orm\Fields;

use DateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Mindy\Orm\ModelInterface;
use Mindy\QueryBuilder\QueryBuilder;
use Symfony\Component\Validator\Constraints as Assert;

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

    /**
     * @return array
     */
    public function getValidationConstraints() : array
    {
        $constraints = [];
        if ($this->null === false) {
            $constraints[] = new Assert\NotBlank();
        }

        $constraints[] = new Assert\Date();

        return $constraints;
    }

    public function beforeInsert(ModelInterface $model, $value)
    {
        if (($this->autoNow || $this->autoNowAdd) && $model->getIsNewRecord()) {
            $model->setAttribute($this->getAttributeName(), new \DateTime());
        }
    }

    public function beforeUpdate(ModelInterface $model, $value)
    {
        if ($this->autoNow && $model->getIsNewRecord() === false) {
            $model->setAttribute($this->getAttributeName(), new \DateTime());
        }
    }

    public function isRequired()
    {
        if ($this->autoNowAdd || $this->autoNow) {
            return false;
        }
        return parent::isRequired();
    }

    public function getValue()
    {
        $adapter = QueryBuilder::getInstance($this->getModel()->getConnection())->getAdapter();
        return $adapter->getDate($this->value);
    }

    /**
     * @param string $fieldClass
     * @return false|null|string
     */
    public function getFormField($fieldClass = '\Mindy\Form\Fields\DateField')
    {
        return parent::getFormField($fieldClass);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (($value instanceof DateTime) == false) {
            $value = (new DateTime())->setTimestamp(is_numeric($value) ? $value : strtotime($value));
        }
        return $this->getSqlType()->convertToDatabaseValue($value, $platform);
    }
}
