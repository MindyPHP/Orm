<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 07/12/2016
 * Time: 17:29.
 */

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\SqlitePlatform;

class IntFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testField()
    {
        $field = new IntField([
            'primary' => true,
        ]);
        $this->assertArrayHasKey('autoincrement', $field->getSqlOptions());

        $field = new IntField([
            'unsigned' => true,
        ]);
        $this->assertArrayHasKey('unsigned', $field->getSqlOptions());

        $platform = new SqlitePlatform();
        $this->assertNull($field->convertToPHPValue(null, $platform));
        $this->assertSame(1, $field->convertToPHPValue('1', $platform));
    }
}
