<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 07:35.
 */

namespace Mindy\Orm\Tests\Databases\Sqlite;

use Mindy\Orm\Tests\Fields\OneToOneFieldTest;

class SqliteOneToOneFieldTest extends OneToOneFieldTest
{
    public $driver = 'sqlite';
}
