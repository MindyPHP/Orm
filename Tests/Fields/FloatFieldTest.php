<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 07/12/2016
 * Time: 17:05.
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
