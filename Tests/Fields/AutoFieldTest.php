<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        $this->assertInstanceOf(Expression::class, $field->convertToDatabaseValue(null, $platform));
    }
}
