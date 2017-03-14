<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
