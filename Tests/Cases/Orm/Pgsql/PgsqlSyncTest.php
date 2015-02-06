<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 19:05
 */

namespace Tests\Cases\Orm\Pgsql;

use Tests\Orm\SyncTest;

class PgsqlSyncTest extends SyncTest
{
    public $driver = 'pgsql';
}
