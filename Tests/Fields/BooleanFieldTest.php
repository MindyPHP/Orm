<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Orm\Fields;

use Mindy\Orm\Fields\BooleanField;

class BooleanFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testField()
    {
        $field = new BooleanField(['default' => false]);

        $options = $field->getSqlOptions();
        $this->assertArrayHasKey('default', $options);

        $this->assertFalse($field->getValue());

        $field->setValue(1);
        $this->assertTrue($field->getValue());
        $field->setValue(true);
        $this->assertTrue($field->getValue());

        $field->setValue(0);
        $this->assertFalse($field->getValue());
        $field->setValue(false);
        $this->assertFalse($field->getValue());

        $field = new BooleanField();
        $this->assertFalse($field->default);

        $field = new BooleanField(['default' => true]);
        $this->assertTrue($field->getValue());
        $field->setValue(new \stdClass());
        $this->assertTrue($field->getValue());
        $this->assertTrue($field->isValid());
    }
}
