<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TimeField.
 */
class TimeField extends Field
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
    public function getValidationConstraints()
    {
        $constraints = [
            new Assert\Time(),
        ];
        if ($this->isRequired()) {
            $constraints[] = new Assert\NotBlank();
        }

        return $constraints;
    }

    /**
     * @return string
     */
    public function getSqlType()
    {
        return Type::getType(Type::TIME);
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
}
