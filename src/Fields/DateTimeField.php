<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DateTimeField
 * @package Mindy\Orm
 */
class DateTimeField extends DateField
{
    /**
     * @return string
     */
    public function getSqlType()
    {
        return Type::getType(Type::DATETIME);
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

        $constraints[] = new Assert\DateTime();

        return $constraints;
    }

    /**
     * @param string $fieldClass
     * @return false|null|string
     */
    public function getFormField($fieldClass = '\Mindy\Form\Fields\DateTimeField')
    {
        return parent::getFormField($fieldClass);
    }
}
