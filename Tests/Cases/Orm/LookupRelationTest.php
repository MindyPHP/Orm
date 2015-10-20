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
 * @date 04/01/14.01.2014 20:41
 */

namespace Tests\Orm;

use Modules\Tests\Models\Customer;
use Modules\Tests\Models\Group;
use Modules\Tests\Models\Membership;
use Modules\Tests\Models\User;
use Tests\OrmDatabaseTestCase;

abstract class LookupRelationTest extends OrmDatabaseTestCase
{
    public $prefix = '';

    protected function getModels()
    {
        return [new User, new Group, new Membership, new Customer];
    }

    public function setUp()
    {
        parent::setUp();

        $group = new Group();
        $group->name = 'Administrators';
        $group->save();

        $group_prog = new Group();
        $group_prog->name = 'Programmers';
        $group_prog->save();

        $anton = new User();
        $anton->username = 'Anton';
        $anton->password = 'Passwords';
        $anton->save();

        $group->users->link($anton);
        $group_prog->users->link($anton);

        $anton_home = new Customer();
        $anton_home->address = "Anton home";
        $anton_home->user = $anton;
        $anton_home->save();

        $anton_work = new Customer();
        $anton_work->address = "Anton work";
        $anton_work->user = $anton;
        $anton_work->save();

        $max = new User();
        $max->username = 'Max';
        $max->password = 'MaxPassword';
        $max->save();

        $group->users->link($max);
        $group_prog->users->link($max);

        $max_home = new Customer();
        $max_home->address = "Max home";
        $max_home->user = $max;
        $max_home->save();

        $model = new Customer();
        $this->prefix = $model->getDb()->tablePrefix;
    }

    public function lookupProvider()
    {
        return [
            [
                Customer::className(),
                ['user__username' => 'Anton'],
                "SELECT COUNT(*) FROM `tests_customer` `tests_customer_1` LEFT OUTER JOIN `tests_user` `tests_user_2` ON `tests_customer_1`.`user_id` = `tests_user_2`.`id` WHERE (`tests_user_2`.`username`='Anton')",
                2
            ],
            [
                Customer::className(),
                ['user__username__startswith' => 'A'],
                "SELECT COUNT(*) FROM `tests_customer` `tests_customer_1` LEFT OUTER JOIN `tests_user` `tests_user_2` ON `tests_customer_1`.`user_id` = `tests_user_2`.`id` WHERE (`tests_user_2`.`username` LIKE 'A%')",
                2
            ],
            [
                Customer::className(),
                ['user__groups__name' => 'Administrators'],
                "SELECT COUNT(DISTINCT `tests_customer_1`.`id`) FROM `tests_customer` `tests_customer_1` LEFT OUTER JOIN `tests_user` `tests_user_2` ON `tests_customer_1`.`user_id` = `tests_user_2`.`id` LEFT OUTER JOIN `tests_membership` `tests_membership_3` ON `tests_user_2`.`id` = `tests_membership_3`.`user_id` LEFT OUTER JOIN `tests_group` `tests_group_4` ON `tests_membership_3`.`group_id` = `tests_group_4`.`id` WHERE (`tests_group_4`.`name`='Administrators')",
                3
            ],
            [
                Customer::className(),
                ['user__groups__name__endswith' => 's'],
                "SELECT COUNT(DISTINCT `tests_customer_1`.`id`) FROM `tests_customer` `tests_customer_1` LEFT OUTER JOIN `tests_user` `tests_user_2` ON `tests_customer_1`.`user_id` = `tests_user_2`.`id` LEFT OUTER JOIN `tests_membership` `tests_membership_3` ON `tests_user_2`.`id` = `tests_membership_3`.`user_id` LEFT OUTER JOIN `tests_group` `tests_group_4` ON `tests_membership_3`.`group_id` = `tests_group_4`.`id` WHERE (`tests_group_4`.`name` LIKE '%s')",
                3
            ],
            [
                User::className(),
                ['addresses__address__contains' => 'Anton'],
                "SELECT COUNT(DISTINCT `tests_user_1`.`id`) FROM `tests_user` `tests_user_1` LEFT OUTER JOIN `tests_customer` `tests_customer_2` ON `tests_user_1`.`id` = `tests_customer_2`.`user_id` WHERE (`tests_customer_2`.`address` LIKE '%Anton%')",
                1
            ],
            [
                Customer::className(),
                ['user__username' => 'Max', 'user__pk' => '2'],
                "SELECT COUNT(*) FROM `tests_customer` `tests_customer_1` LEFT OUTER JOIN `tests_user` `tests_user_2` ON `tests_customer_1`.`user_id` = `tests_user_2`.`id` WHERE (`tests_user_2`.`username`='Max') AND (`tests_user_2`.`id`='2')",
                1
            ],
            [
                Customer::className(),
                ['user__username' => 'Max', 'user__groups__pk' => '1'],
                "SELECT COUNT(DISTINCT `tests_customer_1`.`id`) FROM `tests_customer` `tests_customer_1` LEFT OUTER JOIN `tests_user` `tests_user_2` ON `tests_customer_1`.`user_id` = `tests_user_2`.`id` LEFT OUTER JOIN `tests_membership` `tests_membership_3` ON `tests_user_2`.`id` = `tests_membership_3`.`user_id` LEFT OUTER JOIN `tests_group` `tests_group_4` ON `tests_membership_3`.`group_id` = `tests_group_4`.`id` WHERE (`tests_user_2`.`username`='Max') AND (`tests_group_4`.`id`='1')",
                1
            ]
        ];
    }

    public function testLookupsClean1()
    {
        $filter = ['addresses__address__contains' => 'Anton'];
        $count = 1;
        $qs = User::objects()->filter($filter);
        $this->assertEquals($count, $qs->count());
        $this->assertEquals($count, count($qs->all()));
    }

    public function testLookupsClean()
    {
        $filter = ['user__groups__name__endswith' => 's'];
        $count = 3;
        $qs = Customer::objects()->filter($filter);
        // $sql = "SELECT COUNT(DISTINCT `tests_customer_1`.`id`) FROM `tests_customer` `tests_customer_1` LEFT OUTER JOIN `tests_user` `tests_user_2` ON `tests_customer_1`.`user_id` = `tests_user_2`.`id` LEFT OUTER JOIN `tests_membership` `tests_membership_3` ON `tests_user_2`.`id` = `tests_membership_3`.`user_id` LEFT OUTER JOIN `tests_group` `tests_group_4` ON `tests_membership_3`.`group_id` = `tests_group_4`.`id` WHERE (`tests_group_4`.`name` LIKE '%s')";
        // $this->assertEquals($sql, $qs->countSql());
        $this->assertEquals($count, $qs->count());
        $this->assertEquals($count, count($qs->all()));
    }

    /**
     * @param $cls \Mindy\Orm\Model
     * @dataProvider lookupProvider
     */
    public function testLookups($cls, $filter, $sql, $count)
    {
        $qs = $cls::objects()->filter($filter);
//        $this->assertEquals($sql, $qs->countSql());
        $this->assertEquals($count, $qs->count());
        $this->assertEquals($count, count($qs->all()));
    }
}
