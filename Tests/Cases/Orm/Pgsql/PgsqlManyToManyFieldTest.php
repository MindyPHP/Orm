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

use Tests\Orm\ManyToManyFieldTest;

class PgsqlManyToManyFieldTest extends ManyToManyFieldTest
{
    public $driver = 'pgsql';

    public $manySql = 'SELECT "tests_product_list_2".* FROM "tests_product_list" "tests_product_list_2" JOIN "tests_product_tests_product_list" "tests_product_tests_product_list_1" ON "tests_product_tests_product_list_1"."product_list_id"="tests_product_list_2"."id" WHERE ("tests_product_tests_product_list_1"."product_id"=1)';
}
