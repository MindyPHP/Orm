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

namespace Mindy\Db\Fields;


class CharField extends Field
{
    public $length = 255;

    public function sqlType()
    {
        return 'string(' . (int)$this->length . ')';
    }
}
