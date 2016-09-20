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

    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\DateTimeField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }
}
