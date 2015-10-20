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

use Mindy\Orm\Fields\DateTimeField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Model;
use Tests\OrmDatabaseTestCase;

class User1 extends Model
{
    public static function tableName()
    {
        return "{{tests_user1}}";
    }
}

class Issue extends Model
{
    public static function getFields()
    {
        return [
            'author' => [
                'class' => ForeignField::class,
                'modelClass' => User1::class
            ],
            'user' => [
                'class' => ForeignField::class,
                'modelClass' => User1::class
            ],
            'created_at' => [
                'class' => DateTimeField::class,
                'autoNowAdd' => true
            ]
        ];
    }

    public static function tableName()
    {
        return "{{tests_issue}}";
    }
}

class Issue116Test extends OrmDatabaseTestCase
{
    public $driver = 'pgsql';

    public function getModels()
    {
        return [new User1, new Issue];
    }

    public function testIssue()
    {
        $u = new User1();
        $u->save();
        $this->assertEquals(1, $u->pk);

        $i = new Issue([
            'user' => $u,
            'author' => $u,
        ]);
        $i->save();
        $this->assertEquals(1, $i->pk);
        $this->assertEquals(1, User1::objects()->count());
        $this->assertEquals(1, Issue::objects()->count());

        $qs = Issue::objects()->with(['user', 'author'])->order(['-created_at'])->asArray();
//        $this->assertEquals(implode(' ', [
//            'SELECT "orm_user_3"."id" AS "user__id", "orm_user_2"."id" AS "user__id", "orm_issue_1".*, "orm_issue_1"."created_at"',
//            'FROM "orm_issue" "orm_issue_1"',
//            'LEFT OUTER JOIN "orm_user" "orm_user_2" ON "orm_issue_1"."user_id" = "orm_user_2"."id"',
//            'LEFT OUTER JOIN "orm_user" "orm_user_3" ON "orm_issue_1"."author_id" = "orm_user_3"."id"',
//            'GROUP BY "orm_issue_1"."created_at", "orm_issue_1"."id", "orm_issue_1"."author_id", "orm_issue_1"."user_id", "orm_issue_1"."created_at"',
//            'ORDER BY "orm_issue_1"."created_at" DESC'
//        ]), $qs->allSql());
        $this->assertEquals(1, $qs->count());
        $item = $qs->get();
        unset($item['created_at']);
        $this->assertEquals([
            'author' => [
                'id' => 1
            ],
            'user' => [
                'id' => 1
            ],
            'id' => 1,
            'author_id' => 1,
            'user_id' => 1,
        ], $item);
    }
}
