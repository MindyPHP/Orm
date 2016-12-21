<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 07/12/2016
 * Time: 17:01.
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
