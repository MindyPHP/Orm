<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 07:35.
 */

namespace Mindy\Orm\Tests\Pgsql\Fields;

use Mindy\Orm\Tests\Fields\OneToOneFieldTest;

class PgsqlOneToOneFieldTest extends OneToOneFieldTest
{
    public $driver = 'pgsql';
}
