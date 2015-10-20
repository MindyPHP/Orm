<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 06/02/15 18:58
 */

namespace Tests\Cases\Orm\Pgsql;

use Modules\Tests\Models\Customer;
use Modules\Tests\Models\User;
use Tests\Orm\LookupRelationTest;

class PgsqlLookupRelationTest extends LookupRelationTest
{
    public $driver = 'pgsql';

    public function lookupProvider()
    {
        return [
            [
                Customer::className(),
                ['user__username' => 'Anton'],
                'SELECT COUNT(*) FROM "tests_customer" "tests_customer_1" LEFT OUTER JOIN "tests_user" "tests_user_2" ON "tests_customer_1"."user_id" = "tests_user_2"."id" WHERE ("tests_user_2"."username"=\'Anton\')',
                2
            ],
            [
                Customer::className(),
                ['user__username__startswith' => 'A'],
                'SELECT COUNT(*) FROM "tests_customer" "tests_customer_1" LEFT OUTER JOIN "tests_user" "tests_user_2" ON "tests_customer_1"."user_id" = "tests_user_2"."id" WHERE ("tests_user_2"."username" LIKE \'A%\')',
                2
            ],
            [
                Customer::className(),
                ['user__groups__name' => 'Administrators'],
                'SELECT COUNT(DISTINCT "tests_customer_1"."id") FROM "tests_customer" "tests_customer_1" LEFT OUTER JOIN "tests_user" "tests_user_2" ON "tests_customer_1"."user_id" = "tests_user_2"."id" LEFT OUTER JOIN "tests_membership" "tests_membership_3" ON "tests_user_2"."id" = "tests_membership_3"."user_id" LEFT OUTER JOIN "tests_group" "tests_group_4" ON "tests_membership_3"."group_id" = "tests_group_4"."id" WHERE ("tests_group_4"."name"=\'Administrators\')',
                3
            ],
            [
                Customer::className(),
                ['user__groups__name__endswith' => 's'],
                'SELECT COUNT(DISTINCT "tests_customer_1"."id") FROM "tests_customer" "tests_customer_1" LEFT OUTER JOIN "tests_user" "tests_user_2" ON "tests_customer_1"."user_id" = "tests_user_2"."id" LEFT OUTER JOIN "tests_membership" "tests_membership_3" ON "tests_user_2"."id" = "tests_membership_3"."user_id" LEFT OUTER JOIN "tests_group" "tests_group_4" ON "tests_membership_3"."group_id" = "tests_group_4"."id" WHERE ("tests_group_4"."name" LIKE \'%s\')',
                3
            ],
            [
                User::className(),
                ['addresses__address__contains' => 'Anton'],
                'SELECT COUNT(DISTINCT "tests_user_1"."id") FROM "tests_user" "tests_user_1" LEFT OUTER JOIN "tests_customer" "tests_customer_2" ON "tests_user_1"."id" = "tests_customer_2"."user_id" WHERE ("tests_customer_2"."address" LIKE \'%Anton%\')',
                1
            ],
            [
                Customer::className(),
                ['user__username' => 'Max', 'user__pk' => '2'],
                'SELECT COUNT(*) FROM "tests_customer" "tests_customer_1" LEFT OUTER JOIN "tests_user" "tests_user_2" ON "tests_customer_1"."user_id" = "tests_user_2"."id" WHERE ("tests_user_2"."username"=\'Max\') AND ("tests_user_2"."id"=\'2\')',
                1
            ],
            [
                Customer::className(),
                ['user__username' => 'Max', 'user__groups__pk' => '1'],
                'SELECT COUNT(DISTINCT "tests_customer_1"."id") FROM "tests_customer" "tests_customer_1" LEFT OUTER JOIN "tests_user" "tests_user_2" ON "tests_customer_1"."user_id" = "tests_user_2"."id" LEFT OUTER JOIN "tests_membership" "tests_membership_3" ON "tests_user_2"."id" = "tests_membership_3"."user_id" LEFT OUTER JOIN "tests_group" "tests_group_4" ON "tests_membership_3"."group_id" = "tests_group_4"."id" WHERE ("tests_user_2"."username"=\'Max\') AND ("tests_group_4"."id"=\'1\')',
                1
            ]
        ];
    }
}
