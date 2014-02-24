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
 * @date 04/01/14.01.2014 00:53
 */

namespace Tests\Orm;


use Mindy\Orm\Sync;
use Tests\DatabaseTestCase;
use Tests\Models\CreateModel;
use Tests\Models\ForeignKeyModel;
use Tests\Models\ManyModel;
use Tests\Models\ModelFields;
use Tests\Models\PkModel;
use Tests\Models\Simple;


class SyncTest extends DatabaseTestCase
{
    public function testPk()
    {
        $many = new ManyModel();
        $this->assertEquals(2, count($many->getFieldsInit()));
        $models = [
//            new PkModel(),
//            new ModelFields(),
//            new Simple(),
//            new ForeignKeyModel(),
            new CreateModel(),
            $many,
        ];

        $sync = new Sync($models);
        $sync->delete();

//        foreach($models as $model) {
//            var_dump($model->className());
//            $this->assertFalse($sync->hasTable($model));
//        }

        // Create all tables. If table exists - skip.
        $sync->create();

        foreach($models as $model) {
            $this->assertTrue($sync->hasTable($model));
        }

        // Remove all tables. If table does not exists - skip.
//        $sync->delete();

//        foreach($models as $model) {
//            $this->assertFalse($sync->hasTable($model));
//        }
    }
}
