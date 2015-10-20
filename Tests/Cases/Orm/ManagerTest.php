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
 * @date 04/01/14.01.2014 02:38
 */

namespace Tests\Orm;

use Modules\Tests\Models\Category;
use Tests\OrmDatabaseTestCase;

abstract class ManagerTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new Category];
    }

    public function setUp()
    {
        parent::setUp();

        $model_one = new Category();
        $model_one->name = 'one';
        $model_one->save();

        $model_two = new Category();
        $model_two->name = 'two';
        $model_two->save();
    }

    public function testAll()
    {
        $this->assertEquals(2, count(Category::objects()->all()));
    }

    public function testGet()
    {
        $founded = Category::objects()->get(['name' => 'one']);
        $this->assertInstanceOf('\Modules\Tests\Models\Category', $founded);
        $this->assertEquals('one', $founded->name);
    }

    public function testFilter()
    {
        $this->assertEquals(1, count(Category::objects()->filter(['name' => 'one'])->all()));
    }

    public function testExclude()
    {
        $this->assertEquals(1, count(Category::objects()->exclude(['name' => 'one'])->all()));
    }
}
