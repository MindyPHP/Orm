<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests;

use Mindy\Orm\Tests\Models\BookCategory;

class DataIteratorTest extends OrmDatabaseTestCase
{
    public $driver = 'mysql';

    public function getModels()
    {
        return [
            new BookCategory(),
        ];
    }

    public function testDataQuerySet()
    {
        foreach (range(1, 5) as $i) {
            $model = new BookCategory(['id' => $i]);
            $this->assertEquals($i, $model->id);
            $this->assertTrue($model->isValid());
            $this->assertTrue($model->save());
            $this->assertEquals($i, BookCategory::objects()->count());
            $this->assertEquals($i, $model->id);
        }

        $this->assertEquals(5, BookCategory::objects()->count());

        $qs = BookCategory::objects()->filter(['id__gt' => 0]);
        $this->assertEquals(1, $qs[0]->pk);
        $this->assertEquals(2, $qs[1]->pk);

        $this->assertEquals(5, BookCategory::objects()->count());
        $qs = BookCategory::objects()->all();
        $this->assertEquals(5, count($qs));

        $qs = BookCategory::objects()->filter(['id__gt' => 0])->asArray();
        $this->assertEquals(5, $qs->count());
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model['id']);
        }
        $this->assertEquals(5, $qs->count());
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model['id']);
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
            $model = new BookCategory(['id' => $i]);
            $model->save();
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
