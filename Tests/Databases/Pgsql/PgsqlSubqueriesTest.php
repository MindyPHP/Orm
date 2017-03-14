<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Databases\Pgsql;

use Mindy\Orm\Tests\QueryBuilder\SubqueriesTest;

class PgsqlSubqueriesTest extends SubqueriesTest
{
    public $driver = 'pgsql';
}
