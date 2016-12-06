<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 18:57
 */

namespace Mindy\Tests\Orm\Databases\Sqlite;

use Mindy\Tests\Orm\QueryBuilder\LookupRelationTest;

class SqliteLookupRelationTest extends LookupRelationTest
{
    public $driver = 'sqlite';
}
