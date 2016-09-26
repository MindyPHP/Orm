<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Types\Type;
use Mindy\Orm\ModelInterface;
use Mindy\QueryBuilder\QueryBuilder;

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
     * @param string $fieldClass
     * @return false|null|string
     */
    public function getFormField($fieldClass = '\Mindy\Form\Fields\DateTimeField')
    {
        return parent::getFormField($fieldClass);
    }
}
