<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 15/09/16
 * Time: 16:21.
 */

namespace Tests\Orm\Fields;

use Doctrine\DBAL\Schema\Column;
use Mindy\Orm\Fields\CharField;

class FieldTest extends \PHPUnit_Framework_TestCase
{
    public function testField()
    {
        $field = new CharField([
            'null' => true,
            'length' => 155,
        ]);
//        $this->assertEquals('string', $field->getSqlType());
//        $this->assertEquals([
//            'notnull' => true,
//            'length' => 155,
//        ], $field->getSqlOptions());

        $this->assertInstanceOf(Column::class, $field->getColumn());
    }
}
