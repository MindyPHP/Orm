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
 * @date 16/06/14.06.2014 14:23
 */

namespace Mindy\Orm;


use Mindy\Query\Connection as QueryConnection;

class Connection extends QueryConnection
{
    public function init()
    {
        parent::init();
        Model::setConnection($this);
    }
}
