<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 07/12/2016
 * Time: 17:11.
 */

namespace Tests\Orm\Fields;

use Mindy\Orm\Fields\TimeField;

class TimeFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testField()
    {
        $field = new TimeField();
        $this->assertTrue($field->isRequired());

        $field = new TimeField(['autoNow' => true]);
        $this->assertFalse($field->isRequired());

        $field = new TimeField(['autoNowAdd' => true]);
        $this->assertFalse($field->isRequired());

        $field = new TimeField();
        $field->setValue('foo');
        $this->assertFalse($field->isValid());

        $field->setValue(new \DateTime());
        $this->assertTrue($field->isValid());

        $field->setValue(time());
        $this->assertFalse($field->isValid());

        $field->setValue('2016-10-10');
        $this->assertFalse($field->isValid());

        $field->setValue('2016-10-10 10:10:02');
        $this->assertFalse($field->isValid());

        $field->setValue('10:10:02');
        $this->assertTrue($field->isValid());
    }
}
