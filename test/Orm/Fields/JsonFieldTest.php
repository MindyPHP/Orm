<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 03/01/14.01.2014 22:42
 */

namespace Tests\Orm\Fields;

use Mindy\Orm\Fields\JsonField;

class JsonFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testEncodeDecode()
    {
        $field = new JsonField();
        $field->setValue(['1' => 1]);
        $this->assertTrue(is_array($field->getValue()));
        $this->assertEquals(['1' => 1], $field->getValue());
        $this->assertEquals(1, $field->getValue()['1']);
    }

    public function testValidation()
    {
        $field = new JsonField();
        $field->setValue(new class {});
        $this->assertFalse($field->isValid());
    }
}
