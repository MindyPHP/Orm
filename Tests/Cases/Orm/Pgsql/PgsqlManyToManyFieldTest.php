<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 19:16
 */

namespace Tests\Cases\Orm\Pgsql;

use Modules\Tests\Models\Project;
use Modules\Tests\Models\ProjectMembership;
use Modules\Tests\Models\Worker;
use Tests\Orm\ManyToManyFieldTest;

class PgsqlManyToManyFieldTest extends ManyToManyFieldTest
{
    public $driver = 'pgsql';

    public $manySql = 'SELECT "tests_product_list_2".* FROM "tests_product_list" "tests_product_list_2" JOIN "tests_product_tests_product_list" "tests_product_tests_product_list_1" ON "tests_product_tests_product_list_1"."product_list_id"="tests_product_list_2"."id" WHERE ("tests_product_tests_product_list_1"."product_id"=1)';

    public function testViaOrder()
    {
        $firstProject = new Project();
        $firstProject->name = 'Building';
        $firstProject->save();

        $secondProject = new Project();
        $secondProject->name = 'Logistic';
        $secondProject->save();

        $firstWorker = new Worker();
        $firstWorker->name = 'Mark';
        $firstWorker->save();

        $secondWorker = new Worker();
        $secondWorker->name = 'Alex';
        $secondWorker->save();

        ProjectMembership::objects()->getOrCreate([
            'project' => $firstProject,
            'worker' => $firstWorker,
            'position' => 1,
            'curator' => $secondWorker
        ]);

        ProjectMembership::objects()->getOrCreate([
            'project' => $firstProject,
            'worker' => $secondWorker,
            'position' => 2,
            'curator' => $firstWorker
        ]);

        $this->assertEquals([
            [
                'id' => '1',
                'project_id' => '1',
                'worker_id' => '1',
                'position' => '1',
                'curator_id' => '2',
            ],
            [
                'id' => '2',
                'project_id' => '1',
                'worker_id' => '2',
                'position' => '2',
                'curator_id' => '1',
            ]
        ], ProjectMembership::objects()->asArray()->all());

        $this->assertEquals([
            [
                'id' => '1',
                'name' => 'Mark',
                'position' => 1,
            ],
            [
                'id' => '2',
                'name' => 'Alex',
                'position' => 2
            ]
        ], Worker::objects()->filter(['projects__id__in' => [$firstProject->id]])->order(['projects_through__position'])->asArray()->all());

        $this->assertEquals([
            [
                'id' => '2',
                'name' => 'Alex',
                'position' => 2,
            ],
            [
                'id' => '1',
                'name' => 'Mark',
                'position' => 1,
            ]
        ], Worker::objects()->filter(['projects__id__in' => [$firstProject->id]])->order(['-projects_through__position'])->asArray()->all());

        $this->assertEquals([
            [
                'id' => '1',
                'name' => 'Mark',
                'position' => 1,
            ],
            [
                'id' => '2',
                'name' => 'Alex',
                'position' => 2,
            ],
        ], Worker::objects()->order(['projects_through__position'])->asArray()->all());

        $this->assertEquals([
            [
                'id' => '2',
                'name' => 'Alex',
                'position' => 2,
            ],
            [
                'id' => '1',
                'name' => 'Mark',
                'position' => 1,
            ]
        ], Worker::objects()->order(['-projects_through__position'])->asArray()->all());

        $this->assertEquals([
            [
                'id' => '2',
                'name' => 'Alex',
            ]
        ], Worker::objects()->filter(['projects_through__curator' => $firstWorker])->asArray()->all());

        $this->assertEquals([
            [
                'id' => '1',
                'name' => 'Mark',
            ]
        ], Worker::objects()->filter(['projects_through__curator' => $secondWorker])->asArray()->all());

        $this->assertEquals('SELECT "tests_worker_1".* FROM "tests_worker" "tests_worker_1" LEFT OUTER JOIN "tests_project_membership" "tests_project_membership_2" ON "tests_worker_1"."id" = "tests_project_membership_2"."worker_id" LEFT OUTER JOIN "tests_project" "tests_project_3" ON "tests_project_membership_2"."project_id" = "tests_project_3"."id" WHERE ("tests_project_membership_2"."curator_id"=\'2\') GROUP BY "tests_worker_1"."id"', Worker::objects()->filter(['projects_through__curator' => $secondWorker])->allSql());

        $this->assertEquals('SELECT "tests_worker_1".*, "tests_project_membership_2"."position" FROM "tests_worker" "tests_worker_1" LEFT OUTER JOIN "tests_project_membership" "tests_project_membership_2" ON "tests_worker_1"."id" = "tests_project_membership_2"."worker_id" LEFT OUTER JOIN "tests_project" "tests_project_3" ON "tests_project_membership_2"."project_id" = "tests_project_3"."id" GROUP BY "tests_worker_1"."id", "tests_project_membership_2"."position", "tests_worker_1"."id", "tests_worker_1"."name", "tests_project_3"."id", "tests_project_membership_2"."id" ORDER BY "tests_project_membership_2"."position"', Worker::objects()->order(['projects_through__position'])->asArray()->allSql());

        $this->assertEquals('SELECT "tests_worker_1".*, "tests_project_membership_2"."position" FROM "tests_worker" "tests_worker_1" LEFT OUTER JOIN "tests_project_membership" "tests_project_membership_2" ON "tests_worker_1"."id" = "tests_project_membership_2"."worker_id" LEFT OUTER JOIN "tests_project" "tests_project_3" ON "tests_project_membership_2"."project_id" = "tests_project_3"."id" WHERE ("tests_project_3"."id" IN (\'1\', \'2\')) GROUP BY "tests_worker_1"."id", "tests_project_membership_2"."position", "tests_worker_1"."id", "tests_worker_1"."name", "tests_project_3"."id", "tests_project_membership_2"."id" ORDER BY "tests_project_membership_2"."position" DESC', Worker::objects()->filter(['projects__id__in' => [$firstProject->id, $secondProject->id]])->order(['-projects_through__position'])->allSql());
    }
}
