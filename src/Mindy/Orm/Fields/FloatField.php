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
 * @date 19/05/14.05.2014 13:54
 */

namespace Mindy\Orm\Fields;


class FloatField extends Field
{
    public function sqlType()
    {
        return 'float';
    }
}
