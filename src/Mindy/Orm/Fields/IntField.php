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
 * @date 03/01/14.01.2014 22:01
 */

namespace Mindy\Orm\Fields;


class IntField extends Field
{
    public $length = 11;

    public function setValue($value)
    {
        $this->value = (int)$value;
    }

    public function sqlType()
    {
        return 'int(' . (int)$this->length . ')';
    }
}
