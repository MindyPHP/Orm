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
        $this->assertInstanceOf('\Mindy\Db\Relation', $model->items);
        $this->assertEquals(0, $model->items->count());
        $this->assertEquals([], $model->items->all());

        $this->assertTrue($model->save());
        $this->assertEquals(1, $model->pk);

        $item = new CreateModel();
        $item->name = 'qwe';
        $this->assertTrue($item->save());

        $model->items->link($item);
        $this->assertEquals(1, count($model->items->all()));

        $new = ManyModel::find(1);
        $this->assertEquals(1, count($new->items->all()));
    }
}
