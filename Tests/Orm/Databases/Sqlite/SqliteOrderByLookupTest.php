<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 19:10
 */

namespace Mindy\Tests\Orm\Databases\Sqlite;

use Mindy\Tests\Orm\QueryBuilder\OrderByLookupTest;

class SqliteOrderByLookupTest extends OrderByLookupTest
{
    public $driver = 'sqlite';
}
