<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Fields\Tests;

use Mindy\Orm\Fields\JsonField;
use PHPUnit\Framework\TestCase;

class JsonFieldTest extends TestCase
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
        $field->setValue(new \stdClass());
        $this->assertFalse($field->isValid());
    }
}
