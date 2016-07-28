<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 27/07/16
 * Time: 13:38
 */

namespace Mindy\Orm\Tests\Basic;

use Modules\Tests\Models\Category;
use Modules\Tests\Models\Product;

class SetterGetterTest extends \PHPUnit_Framework_TestCase
{
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

    public function testSetGet()
    {
        $category = new Category();
        $category->name = 'Toys';
        $this->assertSame('Toys', $category->name);

        $product = new Product();
        $product->name = 'Bear';
        $product->price = 100;
        $product->description = 'Funny white bear';
        $this->assertNull($product->category);
        $this->assertNull($product->category_id);

        // Мы не храним полное состояние модели
        $product->category = $category;
        $this->assertNull($product->category);
        $this->assertNull($product->category_id);

        $product->category_id = 1;
        $this->assertSame(1, $product->category_id);
        $this->assertSame(1, $product->getAttribute('category_id'));
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
        $model->getField('something');
    }

    public function testUnknownField()
    {
        $model = new Category();
        $field = $model->getField('something', false);
        $this->assertNull($field);
    }
}