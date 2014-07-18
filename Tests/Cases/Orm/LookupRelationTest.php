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

use Tests\DatabaseTestCase;
use Tests\Models\Customer;
use Tests\Models\Group;
use Tests\Models\Membership;
use Tests\Models\User;

class LookupRelationTest extends DatabaseTestCase
{
    public $prefix = '';

    public function setUp()
    {
        parent::setUp();

        $this->initModels([new User, new Group, new Membership, new Customer]);

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
        $this->prefix = $model->getConnection()->tablePrefix;
    }

    public function tearDown()
    {
        $this->dropModels([new User, new Group, new Membership, new Customer]);
    }

    public function testOneSimple()
    {
        $qs = Customer::objects()->filter(['user__username' => 'Anton']);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}customer` `customer_1` LEFT JOIN `{$this->prefix}user` `user_2` ON `customer_1`.`user_id` = `user_2`.`id` WHERE (`user_2`.`username`='Anton')", $qs->countSql());
        $this->assertEquals(2, $qs->count());
        $this->assertEquals(2, count($qs->all()));
    }

    public function testOneLookup()
    {
        $qs = Customer::objects()->filter(['user__username__startswith' => 'A']);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}customer` `customer_1` LEFT JOIN `{$this->prefix}user` `user_2` ON `customer_1`.`user_id` = `user_2`.`id` WHERE (`user_2`.`username` LIKE 'A%')", $qs->countSql());
        $this->assertEquals(2, $qs->count());
        $this->assertEquals(2, count($qs->all()));
    }

    public function testTwoSimple()
    {
        $qs = Customer::objects()->filter(['user__groups__name' => 'Administrators']);
        $this->assertEquals("SELECT COUNT(DISTINCT `customer_1`.`id`) FROM `{$this->prefix}customer` `customer_1` LEFT JOIN `{$this->prefix}user` `user_2` ON `customer_1`.`user_id` = `user_2`.`id` LEFT JOIN `{$this->prefix}membership` `membership_3` ON `user_2`.`id` = `membership_3`.`user_id` LEFT JOIN `{$this->prefix}group` `group_4` ON `membership_3`.`group_id` = `group_4`.`id` WHERE (`group_4`.`name`='Administrators')", $qs->countSql());
        $this->assertEquals(3, $qs->count());
        $this->assertEquals(3, count($qs->all()));
    }

    public function testTwoLookup()
    {
        $qs = Customer::objects()->filter(['user__groups__name__endswith' => 's']);
        $this->assertEquals("SELECT COUNT(DISTINCT `customer_1`.`id`) FROM `{$this->prefix}customer` `customer_1` LEFT JOIN `{$this->prefix}user` `user_2` ON `customer_1`.`user_id` = `user_2`.`id` LEFT JOIN `{$this->prefix}membership` `membership_3` ON `user_2`.`id` = `membership_3`.`user_id` LEFT JOIN `{$this->prefix}group` `group_4` ON `membership_3`.`group_id` = `group_4`.`id` WHERE (`group_4`.`name` LIKE '%s')", $qs->countSql());
        $this->assertEquals(3, $qs->count());
        $this->assertEquals(3, count($qs->all()));
    }

    public function testHasManySimple()
    {
        $qs = User::objects()->filter(['addresses__address__contains' => 'Anton']);
        $this->assertEquals("SELECT COUNT(DISTINCT `user_1`.`id`) FROM `{$this->prefix}user` `user_1` LEFT JOIN `{$this->prefix}customer` `customer_2` ON `user_1`.`id` = `customer_2`.`user_id` WHERE (`customer_2`.`address` LIKE '%Anton%')", $qs->countSql());
        $this->assertEquals("SELECT `user_1`.* FROM `{$this->prefix}user` `user_1` LEFT JOIN `{$this->prefix}customer` `customer_2` ON `user_1`.`id` = `customer_2`.`user_id` WHERE (`customer_2`.`address` LIKE '%Anton%') GROUP BY `user_1`.`id`", $qs->allSql());
        $this->assertEquals(1, $qs->count());
        $this->assertEquals(1, count($qs->all()));
    }

    public function testTwoFilter()
    {
        $qs = Customer::objects()->filter(['user__username' => 'Max'])->filter(['user__pk' => '2']);
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}customer` `customer_1` LEFT JOIN `{$this->prefix}user` `user_2` ON `customer_1`.`user_id` = `user_2`.`id` WHERE ((`user_2`.`username`='Max')) AND ((`user_2`.`id`='2'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());
        $this->assertEquals(1, count($qs->all()));
    }

    public function testTwoChainedFilter()
    {
        $qs = Customer::objects()->filter(['user__username' => 'Max'])->filter(['user__groups__pk' => '1']);
        $this->assertEquals("SELECT COUNT(DISTINCT `customer_1`.`id`) FROM `{$this->prefix}customer` `customer_1` LEFT JOIN `{$this->prefix}user` `user_2` ON `customer_1`.`user_id` = `user_2`.`id` LEFT JOIN `{$this->prefix}membership` `membership_3` ON `user_2`.`id` = `membership_3`.`user_id` LEFT JOIN `{$this->prefix}group` `group_4` ON `membership_3`.`group_id` = `group_4`.`id` WHERE ((`user_2`.`username`='Max')) AND ((`group_4`.`id`='1'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());
        $this->assertEquals(1, count($qs->all()));
    }
}
