<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
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
