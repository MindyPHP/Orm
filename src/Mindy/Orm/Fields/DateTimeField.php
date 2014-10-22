<?php
/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 03/01/14.01.2014 21:58
 */

namespace Mindy\Orm\Fields;

use Mindy\Query\ConnectionManager;

class DateTimeField extends Field
{
    public $autoNowAdd = false;

    public $autoNow = false;

    public function onBeforeInsert()
    {
        if($this->autoNowAdd) {
            $this->getModel()->setAttribute($this->name, $this->getValue());
        }
    }

    public function onBeforeUpdate()
    {
        if($this->autoNow) {
            $this->getModel()->setAttribute($this->name, $this->getValue());
        }
    }

    public function getValue()
    {
        $db = ConnectionManager::getDb()->getQueryBuilder();
        if(is_numeric($this->value) || $this->autoNowAdd && $this->getModel()->getIsNewRecord() || $this->autoNow) {
            return $db->convertToDateTime();
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

    public function getFormField($form, $fieldClass = null)
    {
        return parent::getFormField($form, \Mindy\Form\Fields\DateTimeField::className());
    }
}
