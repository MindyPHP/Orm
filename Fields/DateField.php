<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Fields;

use DateTime;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Mindy\Orm\ModelInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DateField.
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
     * {@inheritdoc}
     */
    public function getSqlType()
    {
        return Type::getType(Type::DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationConstraints()
    {
        $constraints = [
            new Assert\Date(),
        ];
        if ($this->isRequired()) {
            $constraints[] = new Assert\NotBlank();
        }

        return $constraints;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        if ($this->autoNow || $this->autoNowAdd) {
            return false;
        }

        return parent::isRequired();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeInsert(ModelInterface $model, $value)
    {
        if (($this->autoNow || $this->autoNowAdd) && $model->getIsNewRecord()) {
            $model->setAttribute($this->getAttributeName(), new \DateTime());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeUpdate(ModelInterface $model, $value)
    {
        if ($this->autoNow && $model->getIsNewRecord() === false) {
            $model->setAttribute($this->getAttributeName(), new \DateTime());
        }
    }

    /**
     * {@inheritdoc}
     */
//    public function getValue()
//    {
//        $adapter = QueryBuilder::getInstance($this->getModel()->getConnection())->getAdapter();
//        return $adapter->getDate($this->value);
//    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (($value instanceof DateTime) == false) {
            $value = (new DateTime())->setTimestamp(is_numeric($value) ? $value : strtotime($value));
        }

        return $this->getSqlType()->convertToDatabaseValue($value, $platform);
    }
}
