<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 27/07/16
 * Time: 16:01
 */

namespace Mindy\Orm\Tests\Basic;

use Mindy\Orm\BatchDataIterator;
use Mindy\Orm\Tests\OrmDatabaseTestCase;
use Modules\Tests\Models\User;

class BatchDataIteratorTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new User];
    }

    public function testForeach()
    {
        foreach (range(1, 100) as $i) {
            (new User(['username' => 'user_' . $i, 'password' => 'pass_' . $i]))->save();
        }
        $this->assertEquals(100, User::objects()->count());

        $qs = User::objects();
        $iterator = new BatchDataIterator([
            'qs' => $qs,
            'batchSize' => 10,
            'db' => $this->getConnection(),
            'each' => false,
            'asArray' => true,
        ]);
        $this->assertInstanceOf(BatchDataIterator::class, $iterator);
        foreach ($iterator as $i => $models) {
            $this->assertEquals(10, count($models));
            foreach ($models as $t => $model) {
                $this->assertInstanceOf(User::class, $model);
                $this->assertEquals($i + 1 * $t + 1, $model->pk);
            }
        }
    }

    public function testQuerySet()
    {
        foreach (range(1, 100) as $i) {
            (new User(['username' => 'user_' . $i, 'password' => 'pass_' . $i]))->save();
        }
        $this->assertEquals(100, User::objects()->count());

        foreach (User::objects()->batch(100) as $i => $models) {
            $this->assertEquals(10, count($models));
            foreach ($models as $t => $model) {
                $this->assertInstanceOf(User::class, $model);
                $this->assertEquals($i + 1 * $t + 1, $model->pk);
            }
        }

        foreach (User::objects()->getQuerySet()->batch(100) as $i => $models) {
            $this->assertEquals(10, count($models));
            foreach ($models as $t => $model) {
                $this->assertInstanceOf(User::class, $model);
                $this->assertEquals($i + 1 * $t + 1, $model->pk);
            }
        }
    }
}