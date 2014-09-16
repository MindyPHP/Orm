<?php

namespace Tests\Orm;

use Tests\DatabaseTestCase;
use Tests\Models\Category;
use Tests\Models\Product;

class FieldsAndAttributesGetterSetterTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([
            new Product(),
            new Category()
        ]);
    }

    public function tearDown()
    {
        $this->dropModels([
            new Product(),
            new Category()
        ]);

        parent::tearDown();
    }

    public function testGetter()
    {
        $category = new Category();
        $category->name = 'Toys';
        $category->save();

        $product = new Product();
        $product->name = 'Bear';
        $product->price = 100;
        $product->description = 'Funny white bear';

        $this->assertNull($product->category);
        $product->category = $category;

        $this->assertEquals($product->category_id, $category->pk);
        $this->assertEquals('1', $product->category_id);
        $this->assertEquals('1', $product->category->pk);

        $product->save();

        $this->assertInstanceOf('\Tests\Models\Category', $product->category);
        $this->assertTrue(is_numeric($product->category_id));
        $this->assertEquals(1, $product->category_id);
    }

    public function testSetter()
    {
        $category = new Category();
        $category->name = 'Toys';
        $category->save();

        $product = new Product();
        $product->name = 'Bear';
        $product->price = 100;
        $product->description = 'Funny white bear';
        $this->assertNull($product->category);
        $this->assertNull($product->category_id);

        $this->assertEquals(1, $category->pk);

        // Also working
        // $product->category = $category;
        $product->category_id = $category->pk;

        $this->assertEquals(['id', 'name'], $category->attributes());
        $this->assertEquals(['id', 'name', 'price', 'description', 'category_id'], $product->attributes());

        $this->assertEquals(1, $product->category_id);
        $this->assertEquals(1, $product->getAttribute('category_id'));
        $product->save();

        $this->assertInstanceOf('\Tests\Models\Category', $product->category);
        $this->assertTrue(is_numeric($product->category_id));
    }
}
