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
 * @date 04/01/14.01.2014 03:04
 */

namespace Tests\Orm;


use Tests\DatabaseTestCase;
use Tests\Models\SaveUpdateModel;

class QueryTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new SaveUpdateModel]);

        $this->items = [
            ['name' => 'name 1', 'price' => 1, 'is_default' => true],
            ['name' => 'name 2', 'price' => 2, 'is_default' => true],
            ['name' => 'name 3', 'price' => 3, 'is_default' => false],
        ];
        foreach($this->items as $item) {
            $tmp = new SaveUpdateModel();
            foreach($item as $name => $value) {
                $tmp->$name = $value;
            }
            $tmp->save();
        }
    }

    public function testFind()
    {
        $model = SaveUpdateModel::find();
        $this->assertEquals(3, $model->count());
        $this->assertEquals([
            [
                'id' => 1,
                'name' => 'name 1',
                'price' => 1,
                'is_default' => 1
            ],
            [
                'id' => 2,
                'name' => 'name 2',
                'price' => 2,
                'is_default' => 1
            ],
            [
                'id' => 3,
                'name' => 'name 3',
                'price' => 3,
                'is_default' => 0
            ]
        ], $model->asArray()->all());
    }

    public function testFindWhere()
    {
        $model = SaveUpdateModel::find();
        $this->assertEquals(2, $model->where(['is_default' => true])->count());
        $this->assertEquals([
            [
                'id' => 1,
                'name' => 'name 1',
                'price' => 1,
                'is_default' => 1
            ],
            [
                'id' => 2,
                'name' => 'name 2',
                'price' => 2,
                'is_default' => 1
            ]
        ], $model->asArray()->all());
    }

    public function testFindManager()
    {
        $model = SaveUpdateModel::objects();
        $this->assertEquals(3, $model->count());
        $this->assertEquals([
            [
                'id' => 1,
                'name' => 'name 1',
                'price' => 1,
                'is_default' => 1
            ],
            [
                'id' => 2,
                'name' => 'name 2',
                'price' => 2,
                'is_default' => 1
            ],
            [
                'id' => 3,
                'name' => 'name 3',
                'price' => 3,
                'is_default' => 0
            ]
        ], $model->asArray()->all());
    }

    public function testFindWhereManager()
    {
        $model = SaveUpdateModel::objects();
        $this->assertEquals(2, $model->filter(['is_default' => true])->count());
        $this->assertEquals([
            [
                'id' => 1,
                'name' => 'name 1',
                'price' => 1,
                'is_default' => 1
            ],
            [
                'id' => 2,
                'name' => 'name 2',
                'price' => 2,
                'is_default' => 1
            ]
        ], $model->asArray()->all());
    }
}
