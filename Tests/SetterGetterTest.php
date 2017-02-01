<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests;

use Mindy\Orm\Tests\Models\Category;
use Mindy\Orm\Tests\Models\Product;

class SetterGetterTest extends OrmDatabaseTestCase
{
    public function getModels()
    {
        return [new Product(), new Category()];
    }

    public function testSimple()
    {
        $model = new Product();
        $model->name = 'example';
        $this->assertSame('example', $model->name);
    }

    public function testDefault()
    {
        $model = new Product();
        $this->assertSame('SIMPLE', $model->type);
        $model->type = '123';
        $this->assertSame('123', $model->type);
    }

    public function testSetForeignField()
    {
        $category = new Category();
        $category->name = 'Toys';
        $this->assertSame('Toys', $category->name);

        $product = new Product();
        $this->assertNull($product->category);
        $this->assertNull($product->category_id);

        $product->category = $category;
        $this->assertNull($product->category);
        $this->assertNull($product->category_id);

        $product->category_id = 1;
        $this->assertEquals(1, $product->category_id);
        $this->assertEquals(1, $product->getAttribute('category_id'));
    }

    public function testSetGet()
    {
        $category = new Category(['name' => 'Toys']);
        $this->assertSame('Toys', $category->name);
        $this->assertTrue($category->save());

        $product = new Product();
        $this->assertNull($product->category);
        $this->assertNull($product->category_id);

        // Мы не храним полное состояние модели
        $product->category = $category;
        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals('1', $product->category_id);

        $product->category_id = 1;
        $this->assertEquals(1, $product->category_id);
        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals(1, $product->getAttribute('category_id'));

        $this->assertFalse($category->getIsNewRecord());
        $this->assertFalse($product->category->getIsNewRecord());
    }

    /**
     * @expectedException \Exception
     */
    public function testPropertyException()
    {
        $model = new Product();
        $model->this_property_does_not_exists = 'example';
    }

    /**
     * @expectedException \Exception
     */
    public function testGetFieldException()
    {
        $model = new Category();
        $model->getField('something', true);
    }

    public function testUnknownField()
    {
        $model = new Category();
        $field = $model->getField('something', false);
        $this->assertNull($field);
    }
}
