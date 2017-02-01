<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Tests\Orm\Fields;

use Mindy\Orm\Fields\DateTimeField;

class DateTimeFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testField()
    {
        $field = new DateTimeField();
        $this->assertTrue($field->isRequired());

        $field = new DateTimeField(['autoNow' => true]);
        $this->assertFalse($field->isRequired());

        $field = new DateTimeField(['autoNowAdd' => true]);
        $this->assertFalse($field->isRequired());

        $field = new DateTimeField();
        $field->setValue('foo');
        $this->assertFalse($field->isValid());

        $field->setValue(new \DateTime());
        $this->assertTrue($field->isValid());

        $field->setValue(time());
        $this->assertFalse($field->isValid());

        $field->setValue('2016-10-10');
        $this->assertFalse($field->isValid());

        $field->setValue('2016-10-10 10:10:10');
        $this->assertTrue($field->isValid());
    }
}
