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


class DateTimeField extends Field
{
    public $autoNowAdd = false;

    public $autoNow = false;

    public function getValue()
    {
        $db = $this->getModel()->getConnection()->getQueryBuilder();
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
}
