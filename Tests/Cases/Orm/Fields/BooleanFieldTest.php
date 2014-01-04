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


use Mindy\Db\Fields\BooleanField;
use Tests\TestCase;


class BooleanFieldTest extends TestCase
{
    public function testField()
    {
        $field = new BooleanField(['default' => false]);
        $this->assertFalse($field->getValue());

        $field->setValue(1);
        $this->assertTrue($field->getValue());
        $field->setValue(true);
        $this->assertTrue($field->getValue());

        $field->setValue(0);
        $this->assertFalse($field->getValue());
        $field->setValue(false);
        $this->assertFalse($field->getValue());
    }

    /**
     * @expectedException \Exception
     */
    public function testInitialization()
    {
        new BooleanField();
    }

    public function testObjectValue()
    {
        $field = new BooleanField(['default' => true]);
        $field->setValue(new Test);
        $this->assertTrue($field->getValue());
        $this->assertTrue($field->isValid());
    }
}
