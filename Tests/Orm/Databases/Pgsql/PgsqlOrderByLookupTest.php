<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 19:11
 */

namespace Mindy\Tests\Orm\Databases\Pgsql;

use Mindy\Tests\Orm\QueryBuilder\OrderByLookupTest;

class PgsqlOrderByLookupTest extends OrderByLookupTest
{
    public $driver = 'pgsql';
}
