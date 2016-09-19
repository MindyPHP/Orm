<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 19:24
 */

namespace Mindy\Tests\Orm\Databases\Mysql\Fields;

use Mindy\Tests\Orm\Fields\AutoSlugFieldTest;

class MysqlAutoSlugFieldTest extends AutoSlugFieldTest
{
    public $driver = 'mysql';
}
