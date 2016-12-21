<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 07/12/2016
 * Time: 17:20.
 */

namespace Tests\Orm\Fields;

use Mindy\Orm\Fields\TimestampField;

class TimestampFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testField()
    {
        $field = new TimestampField();

        $field->setValue(time());
        $this->assertTrue($field->isValid());

        $field->setValue(null);
        $this->assertFalse($field->isValid());
    }
}
