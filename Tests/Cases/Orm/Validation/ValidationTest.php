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

use Modules\Tests\Models\Category;
use Modules\Tests\Models\Product;
use Modules\Tests\Models\User;
use Tests\OrmDatabaseTestCase;

class ValidationTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new User, new Product, new Category];
    }

    public function testClass()
    {
        $model = new User();
        $this->assertFalse($model->isValid());
        $this->assertTrue($model->hasErrors());
        $this->assertTrue($model->hasErrors('username'));
        $this->assertEquals(['username' => [
            'Cannot be empty',
            'Minimal length is 3',
        ]], $model->getErrors());
        $this->assertEquals([
            'Cannot be empty',
            'Minimal length is 3',
        ], $model->getErrors('username'));
        $model->clearErrors('username');
        $this->assertEquals([], $model->getErrors());
    }

    public function testClosure()
    {
        $model = new Product();
        $model->setAttributes(['name' => '12']);
        $this->assertFalse($model->isValid());
        $this->assertTrue($model->hasErrors());
        $this->assertTrue($model->hasErrors('name'));
        $this->assertEquals(['name' => ['Minimal length < 3']], $model->getErrors());
        $this->assertEquals(['Minimal length < 3'], $model->getErrors('name'));
        $model->clearErrors('name');
        $this->assertEquals([], $model->getErrors());
    }

    public function testCustomValidation()
    {
        /* @var $nameField \Mindy\Orm\Fields\Field */
        $model = new User();
        $this->assertFalse($model->isValid());
        $this->assertEquals(['username' => [
            'Cannot be empty',
            'Minimal length is 3',
        ]], $model->getErrors());
        $nameField = $model->getField('username');
        $this->assertEquals([
            'Cannot be empty',
            'Minimal length is 3'
        ], $nameField->getErrors());
        $this->assertFalse($nameField->isValid());
        $this->assertEquals([
            'Cannot be empty',
            'Minimal length is 3'
        ], $nameField->getErrors());

        $model->username = 'hi';
        $this->assertEquals('hi', $model->username);
        $this->assertFalse($model->isValid());
        $this->assertEquals('hi', $model->username);

        $model->username = 'This is very long name for bad validation example';
        $model->isValid();
        $this->assertEquals(['username' => ['Maximum length is 20']], $model->getErrors());
        $model->isValid();
        $this->assertEquals(['username' => ['Maximum length is 20']], $model->getErrors());
    }
}
