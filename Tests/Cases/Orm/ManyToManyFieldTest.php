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

    public function testAllSql()
    {
        $qs = ManyModel::objects()->filter(['id' => 1]);
        $this->assertEquals('SELECT * FROM `many_model` WHERE (`id`=1)', $qs->getSql());
        $this->assertEquals('SELECT * FROM `many_model` WHERE (`id`=1)', $qs->allSql());
        $this->assertEquals('SELECT COUNT(*) FROM `many_model` WHERE (`id`=1)', $qs->countSql());
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
        $qs = ManyModel::objects()->filter(['id' => 2]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(0, $qs->count());
        $this->assertEquals('SELECT COUNT(*) FROM `many_model` WHERE (`id`=2)', $qs->countSql());
    }

    public function testIsNull()
    {
        $qs = ManyModel::objects()->filter(['id' => null]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(0, $qs->count());
        $this->assertEquals('SELECT COUNT(*) FROM `many_model` WHERE (`id` IS NULL)', $qs->countSql());
    }

    public function testIn()
    {
        $qs = ManyModel::objects()->filter(['category__in' => [1, 2, 3, 4, 5]]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals('SELECT COUNT(*) FROM `many_model` WHERE (`category` IN (1, 2, 3, 4, 5))', $qs->countSql());
    }

    public function testGte()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filter(['id__gte' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(1, $qs->count());
    }

    public function testGt()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filter(['id__gt' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(0, $qs->count());
    }

    public function testLte()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filter(['id__lte' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(1, $qs->count());
    }

    public function testLt()
    {
        $qs = ManyModel::objects()->filter(['id__lt' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(0, $qs->count());
    }

    public function testContains()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filter(['id__contains' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(1, $qs->count());

        // TODO
        $this->assertEquals("SELECT COUNT(*) FROM `many_model` WHERE (`id` LIKE '%1%')", $qs->countSql());
    }

    public function testStartswith()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filter(['id__startswith' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(1, $qs->count());
        $this->assertEquals("SELECT COUNT(*) FROM `many_model` WHERE (`id` LIKE '%1')", $qs->countSql());
    }

    public function testEndswith()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filter(['id__endswith' => 1]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(1, $qs->count());
        $this->assertEquals("SELECT COUNT(*) FROM `many_model` WHERE (`id` LIKE '1%')", $qs->countSql());
    }

    public function testRange()
    {
        $model = new ManyModel();
        $model->save();
        $this->assertEquals(1, ManyModel::objects()->count());

        $qs = ManyModel::objects()->filter(['id__range' => [0, 1]]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(1, $qs->count());
        $this->assertEquals('SELECT COUNT(*) FROM `many_model` WHERE (`id` BETWEEN 0 AND 1)', $qs->countSql());

        $qs = ManyModel::objects()->filter(['id__range' => [10, 20]]);
        $this->assertInstanceOf('\Mindy\Orm\QuerySet', $qs);
        $this->assertEquals(0, $qs->count());
        $this->assertEquals('SELECT COUNT(*) FROM `many_model` WHERE (`id` BETWEEN 10 AND 20)', $qs->countSql());
    }

    public function testSql()
    {
        $qs = ManyModel::objects()
            ->filter(['name' => 'vasya', 'id__lte' => 2])
            ->filter(['name' => 'petya', 'id__gte' => 4]);

        $this->assertEquals("SELECT COUNT(*) FROM `many_model` WHERE ((`name`='vasya') AND (id <= 4)) AND ((`name`='petya') AND (id >= 4))", $qs->countSql());

        $qs = ManyModel::objects()
            ->filter(['name' => 'vasya', 'id__lte' => 2])
            ->orFilter(['name' => 'petya', 'id__gte' => 4]);

        $this->assertEquals("SELECT COUNT(*) FROM `many_model` WHERE ((`name`='vasya') AND (id <= 4)) OR ((`name`='petya') AND (id >= 4))", $qs->countSql());
    }
}
