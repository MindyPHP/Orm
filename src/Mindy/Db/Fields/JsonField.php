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
 * @date 03/01/14.01.2014 22:02
 */

namespace Mindy\Db\Fields;


class JsonField extends TextField
{
    public function setValue($value)
    {
        if(is_array($value)) {
            $this->value = json_encode($value);
        } else if(is_object($value)) {
            $this->addErrors(["Not json serialize object: " . gettype($value)]);
        }
    }

    public function getValue()
    {
        return json_decode($this->value, true);
    }
}
