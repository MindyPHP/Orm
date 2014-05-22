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


use Tests\DatabaseTestCase;
use Tests\Models\Category;
use Tests\Models\Product;
use Tests\Models\User;
use Tests\TestCase;

class ValidationTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->initModels([new User, new Product, new Category]);
    }

    public function tearDown()
    {
        $this->dropModels([new User, new Product, new Category]);
    }

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
        ], $nameField->getErrors());

        $model->username = 'hi';
        $this->assertEquals('hi', $model->username);
        $this->assertFalse($model->isValid());
        $this->assertEquals('hi', $model->username);

        $model->username = 'This is very long name for bad validation example';
        $model->isValid();
        $this->assertEquals([
            'username' => [
                'Maximum length > 20'
            ]
        ], $model->getErrors());

        $model->isValid();
        $this->assertEquals([
            'username' => [
                'Maximum length > 20'
            ]
        ], $model->getErrors());
    }
}
