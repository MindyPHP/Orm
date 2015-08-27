<?php

namespace Mindy\Orm\Fields;
use Mindy\Query\ConnectionManager;

/**
 * Class DateField
 * @package Mindy\Orm
 */
class DateField extends Field
{
    public $autoFetch = true;

    public $autoNowAdd = false;

    public $autoNow = false;

    public function sqlType()
    {
        return 'date';
    }

    public function onBeforeInsert()
    {
        if ($this->autoNowAdd) {
            $this->getModel()->setAttribute($this->name, $this->getValue());
        }
    }

    public function canBeEmpty()
    {
        return ($this->autoNowAdd || $this->autoNow) || !$this->required && $this->null || !is_null($this->default);
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
            return $db->convertToDate($this->value);
        } else {
            return $this->value;
        }
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
        return parent::getFormField($form, \Mindy\Form\Fields\DateField::className(), $extra);
    }
}
