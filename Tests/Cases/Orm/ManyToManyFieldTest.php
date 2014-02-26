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
 * @date 04/01/14.01.2014 20:41
 */

namespace Tests\Orm;


use Tests\DatabaseTestCase;
use Tests\Models\CreateModel;
use Tests\Models\ManyModel;

class ManyToManyFieldTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new CreateModel, new ManyModel]);
    }

    public function tearDown()
    {
        $this->dropModels([new CreateModel, new ManyModel]);
    }

    public function testSimple()
    {
        $model = new ManyModel();
        $this->assertNull($model->pk);
        $this->assertInstanceOf('\Mindy\Orm\Relation', $model->items);
        $this->assertEquals(0, $model->items->count());
        $this->assertEquals([], $model->items->all());

        $this->assertTrue($model->save());
        $this->assertEquals(1, $model->pk);

        $item = new CreateModel();
        $item->name = 'qwe';
        $this->assertTrue($item->save());

        $model->items->link($item);
        $this->assertEquals(1, count($model->items->all()));

        $new = ManyModel::objects()->get(['id' => 1]);
        $this->assertEquals(1, count($new->items->all()));
    }

    public function testExact()
    {
        $qs = ManyModel::objects()->filterNew(['id' => 2]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(['id' => 2], $qs->where);
        $this->assertEquals(0, $qs->count());
    }

    public function testIsNull()
    {
        $qs = ManyModel::objects()->filterNew(['id' => null]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(['id' => null], $qs->where);
        $this->assertEquals(0, $qs->count());

        // TODO
        $this->assertEquals('SELECT COUNT(*) FROM `many_model` WHERE `category` IS NULL', $qs->sql);
    }

    public function testIn()
    {
        $qs = ManyModel::objects()->filterNew(['category__in' => [1, 2, 3, 4, 5]]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals([
            'in',
            'category',
            [1, 2, 3, 4, 5]
        ], $qs->where);
    }

    public function testGte()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filterNew(['id__gte' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals('id >= :id', $qs->where);
        $this->assertEquals(1, $qs->count());
    }

    public function testGt()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filterNew(['id__gt' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals('id > :id', $qs->where);
        $this->assertEquals(0, $qs->count());
    }

    public function testLte()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filterNew(['id__lte' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals('id <= :id', $qs->where);
        $this->assertEquals(1, $qs->count());
    }

    public function testLt()
    {
        $qs = ManyModel::objects()->filterNew(['id__lt' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals('id < :id', $qs->where);
        $this->assertEquals(0, $qs->count());
    }

    public function testContains()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filterNew(['id__contains' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals([
            'like',
            'id',
            '1'
        ], $qs->where);
        $this->assertEquals(1, $qs->count());

        // TODO
        $this->assertEquals('SELECT * FROM `many_model` WHERE `id` LIKE %1%', $qs->sql);
    }

    public function testStartswith()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filterNew(['id__startswith' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals([
            'like',
            'id',
            '%1'
        ], $qs->where);
        $this->assertEquals(1, $qs->count());

        // TODO
        $this->assertEquals('SELECT * FROM `many_model` WHERE `id` LIKE %1', $qs->sql);
    }

    public function testEndswith()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filterNew(['id__endswith' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals([
            'like',
            'id',
            '1%'
        ], $qs->where);
        $this->assertEquals(1, $qs->count());

        // TODO
        $this->assertEquals('SELECT * FROM `many_model` WHERE `id` LIKE 1%', $qs->sql);
    }

    public function testRange()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filterNew(['id__range' => [0, 1]]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals([
            'between', 'id', 0, 1
        ], $qs->where);
        $this->assertEquals(1, $qs->count());

        $qs = ManyModel::objects()->filterNew(['id__range' => [10, 20]]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals([
            'between', 'id', 10, 20
        ], $qs->where);
        $this->assertEquals(0, $qs->count());
    }
}
