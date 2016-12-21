<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 07/12/2016
 * Time: 17:29.
 */

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Mindy\QueryBuilder\Expression;

class AutoFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testField()
    {
        $field = new AutoField();
        $options = $field->getSqlOptions();
        $this->assertArrayHasKey('notnull', $options);
        $this->assertArrayHasKey('autoincrement', $options);

        $this->assertInstanceOf(BigIntField::class, $field);

        $platform = new PostgreSqlPlatform();
        $this->assertInstanceOf(Expression::class, $field->convertToDatabaseValueSQL(null, $platform));
    }
}
