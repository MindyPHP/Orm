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
        $db = ConnectionManager::getDb()->getQueryBuilder();
        if (is_numeric($this->value) || $this->autoNowAdd && $this->getModel()->getIsNewRecord() || $this->autoNow) {
            return $db->convertToDateTime($this->value);
        } else {
            return $this->value;
        }
    }

    public function sqlType()
    {
        return 'datetime';
    }

    public function getFormField($form, $fieldClass = null, array $extra = [])
    {
        return parent::getFormField($form, \Mindy\Form\Fields\DateTimeField::className(), $extra);
    }
}
