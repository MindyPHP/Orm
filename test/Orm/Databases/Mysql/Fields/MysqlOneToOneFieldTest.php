<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 07:35
 */

namespace Mindy\Tests\Orm\Databases\Mysql\Fields;

use Mindy\Tests\Orm\Fields\OneToOneFieldTest;

class MysqlOneToOneFieldTest extends OneToOneFieldTest
{
    public $driver = 'mysql';
}