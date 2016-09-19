<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 27/07/16
 * Time: 16:40
 */

namespace Mindy\Tests\Orm\Basic;

use Mindy\Orm\Model;

class TableNameModel extends Model
{

}

class VeryLongTableNameModel extends Model
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