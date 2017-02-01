<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Tests\Orm\Fields;

use Mindy\Orm\Fields\DecimalField;

class DecimalFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testDecimal()
    {
        $field = new DecimalField();

        $field->setValue(10);
        $this->assertSame(10.00, $field->getValue());

        $field->setValue(null);
        $this->assertSame(null, $field->getValue());

        $options = $field->getSqlOptions();
        $this->assertArrayHasKey('precision', $options);
        $this->assertArrayHasKey('scale', $options);
    }
}
