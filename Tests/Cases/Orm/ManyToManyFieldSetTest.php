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

class ManyToManyFieldSetTest extends DatabaseTestCase
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
        $model->save();

        $item = new CreateModel();
        $item->name = 'qwe';
        $item->save();

        $pk = $item->pk;

        // Test array of Models
        $model->items = [$item];
        $this->assertEquals(1, $model->items->count());

        // Test empty array
        $model->items = [];
        $this->assertEquals(0, $model->items->count());

        // Test array of pk
        $model->items = [$pk];
        $this->assertEquals(1, $model->items->count());

        // Test clean()
        $model->items->clean();
        $this->assertEquals(0, $model->items->count());
    }
}
