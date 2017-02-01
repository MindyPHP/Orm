<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests\Databases\Sqlite\Fields;

use Mindy\Orm\Tests\Fields\HasManyFieldTest;

class SqliteHasManyFieldTest extends HasManyFieldTest
{
    public $driver = 'sqlite';
}
