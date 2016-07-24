<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 07:35
 */

namespace Mindy\Orm\Tests\Mysql;

use Mindy\Orm\Tests\Fields\OneToOneFieldTest;

class MysqlOneToOneFieldTest extends OneToOneFieldTest
{
    public $driver = 'mysql';
}