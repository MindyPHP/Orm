<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 18:56
 */

namespace Mindy\Tests\Orm\Databases\Pgsql\Fields;

use Mindy\Tests\Orm\Fields\HasManyFieldTest;

class PgsqlHasManyFieldTest extends HasManyFieldTest
{
    public $driver = 'pgsql';
}
