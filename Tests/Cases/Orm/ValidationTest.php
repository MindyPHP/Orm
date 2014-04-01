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
 * @date 03/01/14.01.2014 23:57
 */

namespace Tests\Orm;


use Tests\Models\Product;
use Tests\Models\User;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    public function testClass()
    {
        $model = new User();
        $this->assertFalse($model->isValid());
        $this->assertTrue($model->hasErrors());
        $this->assertTrue($model->hasErrors('username'));
        $this->assertEquals([
            'username' => [
                'NULL is not a string',
                'Minimal length < 3'
            ]
        ], $model->getErrors());

        $this->assertEquals([
            'NULL is not a string',
            'Minimal length < 3'
        ], $model->getErrors('username'));

        $model->clearErrors('username');
        $this->assertEquals([], $model->getErrors());
    }

    public function testClosure()
    {
        $model = new Product();
        $this->assertFalse($model->isValid());
        $this->assertTrue($model->hasErrors());
        $this->assertTrue($model->hasErrors('name'));
        $this->assertEquals([
            'name' => [
                'Minimal length < 3'
            ]
        ], $model->getErrors());

        $this->assertEquals([
            'Minimal length < 3'
        ], $model->getErrors('name'));

        $model->clearErrors('name');
        $this->assertEquals([], $model->getErrors());
    }

    public function testCustomValidation()
    {
        /* @var $nameField \Mindy\Orm\Fields\Field */
        $model = new User();
        $this->assertFalse($model->isValid());
        $this->assertEquals([
            'username' => [
                'NULL is not a string',
                'Minimal length < 3'
            ]
        ], $model->getErrors());

        $nameField = $model->getField('username');
        $this->assertEquals([
            'NULL is not a string',
            'Minimal length < 3'
        ], $nameField->getErrors());
        $this->assertFalse($nameField->isValid());
        $this->assertEquals([
            'NULL is not a string',
            'Minimal length < 3'
        ], $nameField->getErrors(true));

        $model->username = 'hi';
        $this->assertEquals('hi', $model->username);
        $this->assertFalse($nameField->isValid());
        $this->assertEquals(['Minimal length < 3'], $nameField->getErrors());

        $model->username = 'This is very long name for bad validation example';
        $nameField->isValid();
        $this->assertEquals(['Maximum length > 20'], $nameField->getErrors());

        $this->assertEquals([
            'username' => [
                'NULL is not a string',
                'Minimal length < 3'
            ],
        ], $model->getErrors());

        $model->isValid();
        $this->assertEquals([
            'username' => [
                'Maximum length > 20'
            ]
        ], $model->getErrors());
    }
}
