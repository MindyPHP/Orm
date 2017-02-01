<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Tests\Orm\Fields;

use Mindy\Orm\Fields\FloatField;

class FloatFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testFloat()
    {
        $field = new FloatField();
        $field->setValue(1);
        $this->assertSame(1.0, $field->getValue());
    }
}
