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
 * @date 18/07/14.07.2014 17:10
 */

namespace Tests\Orm;

use Tests\DatabaseTestCase;
use Tests\Models\BookCategory;

class DataIteratorTest extends DatabaseTestCase
{
    public function getModels()
    {
        return [new BookCategory];
    }

    public function testDataQuerySet()
    {
        foreach (range(1, 5) as $i) {
            (new BookCategory())->save();
        }

        $this->assertEquals(5, BookCategory::objects()->count());
        $qs = BookCategory::objects()->all();
        $this->assertEquals(5, count($qs));

        $qs = BookCategory::objects()->filter(['id__gt' => 0])->asArray(true);
        $this->assertEquals(5, $qs->count());
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model["id"]);
        }
        $this->assertEquals(5, $qs->count());
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model["id"]);
        }

        $qs = BookCategory::objects()->filter(['id__gt' => 0]);
        $this->assertEquals(5, $qs->count());
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model->pk);
        }
        $this->assertEquals(5, $qs->count());
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model->pk);
        }

        $qs = BookCategory::objects()->filter(['id__gt' => 0]);
        $this->assertEquals(1, $qs[0]->pk);
        $this->assertEquals(2, $qs[1]->pk);
    }

    public function testDataManager()
    {
        foreach (range(1, 5) as $i) {
            (new BookCategory())->save();
        }

        $this->assertEquals(5, BookCategory::objects()->count());
        $qs = BookCategory::objects()->all();
        $this->assertEquals(5, count($qs));

        // Test iterate manager
        $qs = BookCategory::objects();
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model->pk);
        }
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model->pk);
        }

        $qs = BookCategory::objects()->filter(['id__gt' => 0]);
        $this->assertEquals(1, $qs[0]->pk);
        $this->assertEquals(2, $qs[1]->pk);
    }
}
