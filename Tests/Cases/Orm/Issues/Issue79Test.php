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

use Tests\Models\Category;
use Tests\OrmDatabaseTestCase;

class Issue79Test extends OrmDatabaseTestCase
{
    public $driver = 'sqlite';

    public function getModels()
    {
        return [new Category];
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
    }
}
