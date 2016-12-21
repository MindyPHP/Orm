<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 27/07/16
 * Time: 16:01.
 */

namespace Mindy\Orm\Tests;

use Mindy\Orm\BatchDataIterator;
use Mindy\Orm\Tests\Models\User;

class BatchDataIteratorTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new User()];
    }

    public function testEach()
    {
        foreach (range(1, 100) as $i) {
            $user = new User(['id' => $i, 'username' => 'user_'.$i, 'password' => 'pass_'.$i]);
            $user->save();
        }
        $this->assertEquals(100, User::objects()->count());

        $qs = User::objects();
        $iterator = new BatchDataIterator($this->getConnection(), [
            'qs' => $qs,
            'batchSize' => 10,
            'each' => true,
            'asArray' => false,
        ]);
        $this->assertInstanceOf(BatchDataIterator::class, $iterator);
        $id = 1;
        foreach ($iterator as $i => $model) {
            $this->assertInstanceOf(User::class, $model);
            $this->assertEquals($id, $model->pk);
            ++$id;
        }
    }

    public function testForeach()
    {
        foreach (range(1, 100) as $i) {
            (new User(['id' => $i, 'username' => 'user_'.$i, 'password' => 'pass_'.$i]))->save();
        }
        $this->assertEquals(100, User::objects()->count());

        $qs = User::objects();
        $iterator = new BatchDataIterator($this->getConnection(), [
            'qs' => $qs,
            'batchSize' => 10,
            'each' => false,
            'asArray' => false,
        ]);
        $this->assertInstanceOf(BatchDataIterator::class, $iterator);
        $id = 1;
        foreach ($iterator as $i => $models) {
            $this->assertEquals(10, count($models));
            foreach ($models as $t => $model) {
                $this->assertInstanceOf(User::class, $model);
                $this->assertEquals($id, $model->pk);
                ++$id;
            }
        }
    }

    public function testQuerySet()
    {
        foreach (range(1, 100) as $i) {
            (new User(['id' => $i, 'username' => 'user_'.$i, 'password' => 'pass_'.$i]))->save();
        }
        $this->assertEquals(100, User::objects()->count());

        $id = 1;
        foreach (User::objects()->batch(25) as $i => $models) {
            $this->assertEquals(25, count($models));
            foreach ($models as $t => $model) {
                $this->assertInstanceOf(User::class, $model);
                $this->assertEquals($id, $model->pk);
                ++$id;
            }
        }

        $id = 1;
        foreach (User::objects()->getQuerySet()->batch(20) as $i => $models) {
            $this->assertEquals(20, count($models));
            foreach ($models as $t => $model) {
                $this->assertInstanceOf(User::class, $model);
                $this->assertEquals($id, $model->pk);
                ++$id;
            }
        }
    }
}
