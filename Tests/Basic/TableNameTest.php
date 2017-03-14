<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Basic;

use Mindy\Orm\AbstractModel;

class TableNameModel extends AbstractModel
{
}

class VeryLongTableNameModel extends AbstractModel
{
}

class TableNameTest extends \PHPUnit_Framework_TestCase
{
    public function testTableName()
    {
        $this->assertEquals('table_name_model', TableNameModel::tableName());
    }

    public function testLongTableName()
    {
        $this->assertEquals('very_long_table_name_model', VeryLongTableNameModel::tableName());
    }
}
