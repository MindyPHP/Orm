<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
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
