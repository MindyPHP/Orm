<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
