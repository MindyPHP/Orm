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
 * @date 04/01/14.01.2014 00:53
 */

namespace Tests\Orm;

use Mindy\Orm\Fields\MarkdownField;
use Mindy\Orm\Fields\MarkdownHtmlField;
use Tests\Models\Category;
use Tests\Models\Customer;
use Tests\Models\CustomPk;
use Tests\Models\Group;
use Tests\Models\Hits;
use Tests\Models\InstanceTestModel;
use Tests\Models\MarkdownModel;
use Tests\Models\Membership;
use Tests\Models\Product;
use Tests\Models\ProductList;
use Tests\Models\Solution;
use Tests\Models\User;
use Tests\OrmDatabaseTestCase;

abstract class BasicTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [
            new Product,
            new Category,
            new CustomPk,
            new MarkdownModel,
            new InstanceTestModel,
            new User,
            new Hits,
            new Group,
            new Customer,
            new Membership,
            new Solution
        ];
    }

    public function testFieldPrimaryKey()
    {
        $category = new Category();
        $fields = $category->getFieldsInit();

        $this->assertEquals(['id', 'name', 'products'], array_keys($fields));

        $this->assertInstanceOf('\Mindy\Orm\Fields\AutoField', $fields['id']);
        $this->assertNull($category->id);
        $this->assertNull($category->pk);
    }

    public function testCustomPrimaryKeySave()
    {
        $model = new CustomPk();
        $this->assertTrue($model->getIsNewRecord());
        $model->id = 1;
        $this->assertTrue($model->getIsNewRecord());
        $saved = $model->save();
        $this->assertTrue($saved);
        $this->assertFalse($model->getIsNewRecord());
        $model->id = 2;
        $this->assertTrue($model->getIsNewRecord());

        $model = CustomPk::objects()->filter(['pk' => 1])->get();
        $this->assertFalse($model->getIsNewRecord());
        $model->id = 3;
        $this->assertTrue($model->getIsNewRecord());

        $model = CustomPk::objects()->filter(['pk' => 1])->get();
        $this->assertFalse($model->getIsNewRecord());
        $model->pk = 4;
        $this->assertTrue($model->getIsNewRecord());
    }

    public function testExtraFieldsMarkdown()
    {
        $model = new MarkdownModel();

        $fields = $model->getFieldsInit();
        $this->assertEquals(3, count($fields));

        $this->assertInstanceOf(MarkdownField::className(), $model->getField('content'));
        $this->assertInstanceOf(MarkdownHtmlField::className(), $model->getField('content_html'));

        $model->content = "# Hello world";
        $this->assertEquals("# Hello world", $model->content_html);
        $this->assertEquals("<h1>Hello world</h1>\n", $model->getField('content_html')->getDbPrepValue());
        $model->save();

        $fetchModel = MarkdownModel::objects()->filter(['pk' => 1])->get();
        $this->assertEquals("# Hello world", $fetchModel->content);
        $this->assertEquals("<h1>Hello world</h1>\n", $fetchModel->content_html);
    }

    public function testGetter()
    {
        $model = new Product();
        $this->assertEquals('SIMPLE', $model->type);

        // test default field value
        $this->assertEquals('Product', $model->name);
        $this->assertTrue($model->save());
        $this->assertEquals('Product', $model->name);

        $model->name = '123';

        $this->assertTrue($model->save());
        $this->assertEquals('123', $model->name);

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
        $model = new Product();
        $model->name = 'example';
        $this->assertEquals('example', $model->name);

        $this->assertEquals('SIMPLE', $model->type);
        $model->type = '123';
        $this->assertEquals('123', $model->type);

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

    /**
     * @expectedException \Exception
     */
    public function testSettersException()
    {
        $model = new Product();
        $model->this_property_does_not_exists = 'example';
    }

    public function testInitialization()
    {
        $model = new Category();
        $this->assertEquals(2, count($model->getFields()));
        $this->assertEquals(3, count($model->getFieldsInit()));
    }

    /**
     * @expectedException \Exception
     */
    public function testUnknownFieldException()
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

    public function testInstance()
    {
        $this->assertInstanceOf('\Mindy\Orm\Manager', InstanceTestModel::objects());
        $model = new InstanceTestModel;
        $this->assertEquals(123, $model->objects());
    }

    public function testDirtyAttributes()
    {
        $user = new User();
        $this->assertTrue($user->getIsNewRecord());

        $user->username = '123';
        $user->password = '123';

        $this->assertEquals([
            'username' => '123',
            'password' => '123'
        ], $user->getDirtyAttributes());

        $saved = $user->save();
        $this->assertTrue($saved);

        $this->assertFalse($user->getIsNewRecord());
    }

    public function testTableName()
    {
        $this->assertEquals('{{%tests_product}}', Product::tableName());
    }

    public function testLongTableName()
    {
        $this->assertEquals('{{%tests_product_list}}', ProductList::tableName());
    }

    public function testUpdateCounters()
    {
        $model = new Hits();
        $model->save();
        $this->assertEquals(1, $model->pk);
        $this->assertEquals(0, $model->hits);
        $this->assertEquals(0, Hits::objects()->get(['pk' => 1])->hits);

        $model->objects()->updateCounters(['hits' => 1]);
        $this->assertEquals(1, Hits::objects()->get(['pk' => 1])->hits);
    }

    public function testForeignKey()
    {
        $model = new Product();
        $schema = $model->getTableSchema();
        $this->assertTrue(isset($schema->columns['id']));
        $this->assertTrue(isset($schema->columns['category_id']));

        $fk = $model->getField("category");
        $this->assertInstanceOf('\Mindy\Orm\Fields\ForeignField', $fk);
        $this->assertNull($model->category);
    }

    public function testValuesList()
    {
        $group = new Group();
        $group->name = 'Administrators';
        $group->save();

        $groupProg = new Group();
        $groupProg->name = 'Programmers';
        $groupProg->save();

        $anton = new User();
        $anton->username = 'Anton';
        $anton->password = 'Passwords';
        $anton->save();

        $groupProg->users->link($anton);

        $anton_home = new Customer();
        $anton_home->address = "Anton home";
        $anton_home->user = $anton;
        $anton_home->save();

        $anton_work = new Customer();
        $anton_work->address = "Anton work";
        $anton_work->user = $anton;
        $anton_work->save();

        $max = new User();
        $max->username = 'Max';
        $max->password = 'MaxPassword';
        $max->save();

        $group->users->link($max);

        $max_home = new Customer();
        $max_home->address = "Max home";
        $max_home->user = $max;
        $max_home->save();

        $values = Customer::objects()->valuesList(['address', 'user__username']);
        $this->assertEquals([
            ['address' => 'Anton home', 'user__username' => 'Anton'],
            ['address' => "Anton work", 'user__username' => 'Anton'],
            ['address' => "Max home", 'user__username' => 'Max'],
        ], $values);

        $this->assertEquals([
            ['address' => 'Anton home', 'user__username' => 'Anton'],
            ['address' => "Anton work", 'user__username' => 'Anton'],
            ['address' => "Max home", 'user__username' => 'Max'],
        ], Customer::objects()->valuesList(['address', 'user__username']));
        $this->assertEquals([
            'Anton',
            'Anton',
            'Max',
        ], Customer::objects()->valuesList(['user__username'], true));
    }

    public function testToArray()
    {
        $solution = Solution::objects()->getOrCreate([
            'status' => 1,
            'name' => 'test',
            'court' => 'qwe',
            'question' => 'qwe',
            'result' => 'qwe',
            'content' => 'qwe',
        ]);

        $array = $solution->toArray();
        unset($array['created_at']);

        $this->assertEquals([
            'id' => '1',
            'name' => 'test',
            'court' => 'qwe',
            'question' => 'qwe',
            'result' => 'qwe',
            'document' => null,
            'content' => 'qwe',
            'status' => 1,
            'status__text' => 'Complete'
        ], $array);

        $solution->status = Solution::STATUS_SUCCESS;

        $array = $solution->toArray();
        unset($array['created_at']);

        $this->assertEquals([
            'id' => '1',
            'name' => 'test',
            'court' => 'qwe',
            'question' => 'qwe',
            'result' => 'qwe',
            'document' => null,
            'content' => 'qwe',
            'status' => 2,
            'status__text' => 'Successful'
        ], $array);

        $solution->save();

        $array = $solution->toArray();
        unset($array['created_at']);

        $this->assertEquals([
            'id' => '1',
            'name' => 'test',
            'court' => 'qwe',
            'question' => 'qwe',
            'result' => 'qwe',
            'document' => null,
            'content' => 'qwe',
            'status' => 2,
            'status__text' => 'Successful'
        ], $array);
    }
}
