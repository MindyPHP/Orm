<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 16/02/15 17:38
 */

namespace Tests\Cases\Orm\Issues;

use Mindy\Query\ConnectionManager;
use Modules\Tests\Models\Category;
use Modules\Tests\Models\ModelWheel;
use Tests\OrmDatabaseTestCase;

class Issue79Test extends OrmDatabaseTestCase
{
    public $driver = 'mysql';

    public function getModels()
    {
        return [new Category, new ModelWheel];
    }

    public function setUp()
    {
        parent::setUp();

        foreach (range(0, 11) as $i) {
            (new Category(['name' => 'foo' . $i]))->save();
        }
    }

    public function testIssue79()
    {
        $c = Category::objects()->batch(2);
        foreach ($c as $categories) {
            $this->assertEquals(2, count($categories));
        }

        $sql = Category::objects()->filter(['pk__in' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]])->allSql();
        $this->assertTrue(is_string($sql));
        $this->assertEquals($sql, 'SELECT `tests_category_1`.* FROM `tests_category` `tests_category_1` WHERE (`tests_category_1`.`id` IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10))');
        $c = Category::objects()->filter(['pk__in' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]])->batch(2);
        foreach ($c as $categories) {
            $this->assertEquals(2, count($categories));
        }

        $c = Category::objects()->filter(['pk__in' => [1, 2, 3, 4]])->batch(2);
        $total = 0;
        foreach ($c as $categories) {
            $total += count($categories);
            $this->assertEquals(2, count($categories));
        }
        $this->assertEquals(4, $total);
    }

    public function testIssue79RealData()
    {
        $data = [
            ['name' => 'OPL2', 'upper_name' => 'OPL2', 'producer_wheel_id' => 1, 'type' => 2, 'color' => 's', 'image' => 'Mir/ModelWheel/2015-02-22/305df68c4cc4b7e88edecbd309ebefd7_0.jpg'],
            ['name' => 'SNG13', 'upper_name' => 'SNG13', 'producer_wheel_id' => 1, 'type' => 2, 'color' => 's', 'image' => null],
            ['name' => 'HND138', 'upper_name' => 'HND138', 'producer_wheel_id' => 1, 'type' => 2, 'color' => 's', 'image' => 'Mir/ModelWheel/2015-02-22/8d6653d486a8fc9a530fea3b65862b5b_0.jpg'],
            ['name' => 'RN63', 'upper_name' => 'RN63', 'producer_wheel_id' => 1, 'type' => 2, 'color' => 'mbf', 'image' => 'Mir/ModelWheel/2015-02-22/b4e7d8f3c2bbae342bed7d67923efcd4_0.jpg'],
            ['name' => 'NS68', 'upper_name' => 'NS68', 'producer_wheel_id' => 1, 'type' => 2, 'color' => 's', 'image' => 'Mir/ModelWheel/2015-02-22/d4cf1db874a08800cc08cb63a3617692_0.jpg'],
            ['name' => 'GM55', 'upper_name' => 'GM55', 'producer_wheel_id' => 1, 'type' => 2, 'color' => 's', 'image' => 'Mir/ModelWheel/2015-02-22/a796594ecfcb558e5141d34645f95c61_0.jpg'],
            ['name' => 'NS85', 'upper_name' => 'NS85', 'producer_wheel_id' => 1, 'type' => 2, 'color' => 's', 'image' => 'Mir/ModelWheel/2015-02-22/0c78f88b3491ae3eec2c709553cec65d_0.jpg'],
            ['name' => 'TY110', 'upper_name' => 'TY110', 'producer_wheel_id' => 2, 'type' => 2, 'color' => 's', 'image' => 'Mir/ModelWheel/2015-02-22/c4def2a574de23f0eb752c696908fc96_0.jpg'],
            ['name' => 'VW122', 'upper_name' => 'VW122', 'producer_wheel_id' => 1, 'type' => 2, 'color' => 'unknown', 'image' => null],
        ];

        foreach($data as $item) {
            (new ModelWheel($item))->save();
        }

        $this->assertEquals(9, ModelWheel::objects()->count());
        $this->assertEquals(7, ModelWheel::objects()->filter(['image__isnull' => false])->count());

        $total = 0;
        $batchModels = ModelWheel::objects()->filter(['image__isnull' => false])->batch();
        foreach ($batchModels as $models) {
            $total += count($models);
        }
        $this->assertEquals(7, $total);

        $total = 0;
        $batchModels = ModelWheel::objects()->filter(['pk__in' => [1, 2, 3]])->batch();
        foreach ($batchModels as $models) {
            $total += count($models);
        }
        $this->assertEquals(3, $total);
    }
}
