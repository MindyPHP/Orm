<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use PHPUnit\Framework\TestCase;

class IntFieldTest extends TestCase
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
