<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 *
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 18:08
 */

namespace Mindy\Orm\Tests\Databases\Pgsql;

use Mindy\Orm\Tests\Basic\CrudTest;

class PgsqlCrudTest extends CrudTest
{
    public $driver = 'pgsql';
}
