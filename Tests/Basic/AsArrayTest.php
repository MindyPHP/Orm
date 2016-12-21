<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 27/07/16
 * Time: 16:44.
 */

namespace Mindy\Orm\Tests\Basic;

use Mindy\Orm\Tests\OrmDatabaseTestCase;
use Mindy\Orm\Tests\Models\Solution;

abstract class AsArrayTest extends OrmDatabaseTestCase
{
    public function getModels()
    {
        return [new Solution()];
    }

    public function testToArray()
    {
        $model = new Solution([
            'status' => 1,
            'name' => 'test',
            'court' => 'qwe',
            'question' => 'qwe',
            'result' => 'qwe',
            'content' => 'qwe',
        ]);
        $this->assertEquals(1, count($model->getField('document')->getValidators()));
        $this->assertEquals(true, $model->isValid());

        list($solution, $created) = Solution::objects()->getOrCreate([
            'status' => 1,
            'name' => 'test',
            'court' => 'qwe',
            'question' => 'qwe',
            'result' => 'qwe',
            'content' => 'qwe',
        ]);

        $array = $solution->toArray();
        unset($array['created_at']);

        $this->assertEquals([
            'id' => '1',
            'name' => 'test',
            'court' => 'qwe',
            'question' => 'qwe',
            'result' => 'qwe',
            'document' => null,
            'content' => 'qwe',
            'status' => 1,
            'status__text' => 'Complete',
        ], $array);

        $solution->status = Solution::STATUS_SUCCESS;

        $array = $solution->toArray();
        unset($array['created_at']);

        $this->assertEquals([
            'id' => '1',
            'name' => 'test',
            'court' => 'qwe',
            'question' => 'qwe',
            'result' => 'qwe',
            'document' => null,
            'content' => 'qwe',
            'status' => 2,
            'status__text' => 'Successful',
        ], $array);

        $solution->save();

        $array = $solution->toArray();
        unset($array['created_at']);

        $this->assertEquals([
            'id' => '1',
            'name' => 'test',
            'court' => 'qwe',
            'question' => 'qwe',
            'result' => 'qwe',
            'document' => null,
            'content' => 'qwe',
            'status' => 2,
            'status__text' => 'Successful',
        ], $array);
    }
}
