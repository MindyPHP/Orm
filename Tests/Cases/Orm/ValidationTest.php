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


use Tests\Models\ClassValidationModel;
use Tests\Models\ClosureValidationModel;
use Tests\Models\ValidationModel;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    public function testClass()
    {
        $model = new ClassValidationModel();
        $this->assertFalse($model->isValid());
        $this->assertTrue($model->hasErrors());
        $this->assertTrue($model->hasErrors('name'));
        $this->assertEquals([
            'name' => [
                'NULL is not a string',
                'Minimal length < 6'
            ]
        ], $model->getErrors());

        $this->assertEquals([
            'NULL is not a string',
            'Minimal length < 6'
        ], $model->getErrors('name'));

        $model->clearErrors('name');
        $this->assertEquals([], $model->getErrors());
    }

    public function testClosure()
    {
        $model = new ClosureValidationModel();
        $this->assertFalse($model->isValid());
        $this->assertTrue($model->hasErrors());
        $this->assertTrue($model->hasErrors('name'));
        $this->assertEquals([
            'name' => [
                'Minimal length < 6'
            ]
        ], $model->getErrors());

        $this->assertEquals([
            'Minimal length < 6'
        ], $model->getErrors('name'));

        $model->clearErrors('name');
        $this->assertEquals([], $model->getErrors());
    }

    public function testCustomValidation()
    {
        /* @var $nameField \Mindy\Db\Fields\Field */
        $model = new ValidationModel();
        $this->assertFalse($model->isValid());
        $this->assertEquals([
            'name' => [
                'NULL is not a string',
                'Minimal length < 6'
            ]
        ], $model->getErrors());

        $nameField = $model->getField('name');
        $this->assertEquals([
            'NULL is not a string',
            'Minimal length < 6'
        ], $nameField->getErrors());
        $this->assertFalse($nameField->isValid());
        $this->assertEquals([
            'NULL is not a string',
            'Minimal length < 6'
        ], $nameField->getErrors(true));

        $model->name = 'hello';
        $this->assertEquals('hello', $model->name);
        $this->assertFalse($nameField->isValid());
        $this->assertEquals(['Minimal length < 6'], $nameField->getErrors());

        $model->name = '01234689_10';
        $nameField->isValid();
        $this->assertEquals(['Maximum name field is 10'], $nameField->getErrors());

        $this->assertEquals([
            'name' => [
                'NULL is not a string',
                'Minimal length < 6'
            ],
        ], $model->getErrors());

        $model->isValid();
        $this->assertEquals([
            'name' => [
                'Maximum name field is 10'
            ]
        ], $model->getErrors());
    }
}
