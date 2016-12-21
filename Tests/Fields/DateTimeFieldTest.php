<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 07/12/2016
 * Time: 16:42.
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
