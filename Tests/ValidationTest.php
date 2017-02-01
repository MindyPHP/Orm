<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests;

use Mindy\Orm\Tests\Models\Product;
use Mindy\Orm\Tests\Models\User;

class ValidationTest extends OrmDatabaseTestCase
{
    public function testClass()
    {
        $model = new User();
        $this->assertFalse($model->isValid());
        $this->assertEquals(1, count($model->getErrors()));
        $this->assertEquals(['username' => [
            'This value should not be blank.',
        ]], $model->getErrors());
        $model->username = '123456';
        $this->assertSame('123456', $model->username);
        $this->assertTrue($model->isValid());
        $this->assertEquals([], $model->getErrors());
    }

    public function testClosure()
    {
        $model = new Product();
        $model->setAttributes(['name' => '12']);
        $this->assertFalse($model->isValid());
        $this->assertEquals(1, count($model->getErrors()));
        $this->assertEquals(['name' => ['This value is too short. It should have 3 characters or more.']], $model->getErrors());
        $model->setAttributes(['name' => '123']);
        $this->assertTrue($model->isValid());
        $this->assertEquals([], $model->getErrors());
    }

    public function testCustomValidation()
    {
        /* @var $nameField \Mindy\Orm\Fields\Field */
        $model = new User();
        $this->assertFalse($model->isValid());

        $this->assertEquals(['username' => [
            'This value should not be blank.',
        ]], $model->getErrors());

        $nameField = $model->getField('username');
        $this->assertEquals([
            'This value should not be blank.',
        ], $nameField->getErrors());

        $model->username = 'hi';
        $this->assertEquals('hi', $model->username);
        $this->assertFalse($model->isValid());
        $this->assertEquals('hi', $model->username);

        $model->username = 'This is very long name for bad validation example';
        $model->isValid();
        $this->assertEquals(['username' => ['This value is too long. It should have 20 characters or less.']], $model->getErrors());
    }
}
