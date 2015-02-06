<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 18:45
 */

namespace Tests\Cases\Orm\Mysql;

use Mindy\Query\ConnectionManager;
use Tests\Orm\BasicTest;

class MysqlBasicTest extends BasicTest
{
    public $driver = 'mysql';
}
