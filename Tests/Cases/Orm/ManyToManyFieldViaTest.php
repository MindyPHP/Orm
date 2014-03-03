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
use Tests\Models\ManyViaModel;
use Tests\Models\ViaManyModel;

class ManyToManyFieldViaTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new CreateModel, new ManyViaModel, new ViaManyModel]);
    }

    public function tearDown()
    {
        $this->dropModels([new CreateModel, new ManyViaModel, new ViaManyModel]);
    }

    public function testSimple()
    {
        $model = new ManyViaModel();
        $this->assertNull($model->pk);
        $this->assertInstanceOf('\Mindy\Orm\ManyToManyManager', $model->items);
        $this->assertEquals(0, $model->items->count());
        $this->assertEquals([], $model->items->all());

        $this->assertTrue($model->save());
        $this->assertEquals(1, $model->pk);

        $item = new CreateModel();
        $item->name = 'qwe';
        $this->assertTrue($item->save());

        $this->assertEquals(0, count($model->items->all()));

        $model->items->link($item);
        $this->assertEquals(1, count($model->items->all()));

        $new = ManyViaModel::objects()->get(['id' => 1]);

        $this->assertEquals(1, count($new->items->all()));
    }
}
