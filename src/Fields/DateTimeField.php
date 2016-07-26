<?php

namespace Mindy\Orm\Fields;

use Mindy\Query\ConnectionManager;

/**
 * Class DateTimeField
 * @package Mindy\Orm
 */
class DateTimeField extends DateField
{
    public function getValue()
    {
        /** @var \Mindy\QueryBuilder\BaseAdapter $db */
        $db = $this->getModel()->getDb()->getAdapter();
        if ($this->autoNowAdd && $this->getModel()->getIsNewRecord() || $this->autoNow) {
            return $db->getDateTime();
        }

        if (is_numeric($this->value)) {
            return $db->getDateTime($this->value);
        }

        return $this->value;
    }

    public function sqlType()
    {
        return 'datetime';
    }

    public function getFormField($form, $fieldClass = '\Mindy\Form\Fields\DateTimeField', array $extra = [])
    {
        return parent::getFormField($form, $fieldClass, $extra);
    }
}
