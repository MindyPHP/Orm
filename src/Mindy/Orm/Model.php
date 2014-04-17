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
 * @date 03/01/14.01.2014 21:52
 */

namespace Mindy\Orm;


class Model extends Orm
{
    public function set(array $values)
    {
        foreach($values as $name => $value) {
            $this->{$name} = $value;
        }
        return $this;
    }
}
