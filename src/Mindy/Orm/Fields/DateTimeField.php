<?php

namespace Mindy\Orm\Fields;

use Mindy\Query\ConnectionManager;

/**
 * Class DateTimeField
 * @package Mindy\Orm
 */
class DateTimeField extends Field
{
    public $autoFetch = true;

    public $autoNowAdd = false;

    public $autoNow = false;

    public function onBeforeInsert()
    {
        if ($this->autoNowAdd) {
            $this->getModel()->setAttribute($this->name, $this->getValue());
        }
    }

    public function onBeforeUpdate()
    {
        if ($this->autoNow) {
            $this->getModel()->setAttribute($this->name, $this->getValue());
        }
    }

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

    public function sqlDefault()
    {
        return $this->default === null ? '' : "DEFAULT '{$this->default}'";
    }

    public function sqlNullable()
    {
        return $this->autoNow ? 'NULL' : parent::sqlNullable();
    }

    public function getFormField($form, $fieldClass = null, array $extra = [])
    {
        return parent::getFormField($form, \Mindy\Form\Fields\DateTimeField::className(), $extra);
    }
}
