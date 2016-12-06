<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 07:35
 */

namespace Mindy\Tests\Orm\Pgsql\Fields;

use Mindy\Tests\Orm\Fields\OneToOneFieldTest;

class PgsqlOneToOneFieldTest extends OneToOneFieldTest
{
    public $driver = 'pgsql';
}