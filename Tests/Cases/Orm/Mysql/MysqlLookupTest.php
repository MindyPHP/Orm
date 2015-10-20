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

namespace Tests\Orm\Mysql;

use Modules\Tests\Models\ProductList;
use Tests\Orm\LookupTest;

class MysqlLookupTest extends LookupTest
{
    public $driver = 'mysql';

    public $prefix = 'tests_';

    public function testYear()
    {
        $qs = ProductList::objects()->filter(['date_action__year' => 2014]);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((EXTRACT(YEAR FROM `tests_product_list_1`.`date_action`) = '2014'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__year' => '2013']);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((EXTRACT(YEAR FROM `tests_product_list_1`.`date_action`) = '2013'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testMonth()
    {
        $qs = ProductList::objects()->filter(['date_action__month' => 4]);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((EXTRACT(MONTH FROM `tests_product_list_1`.`date_action`) = '4'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__month' => '3']);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((EXTRACT(MONTH FROM `tests_product_list_1`.`date_action`) = '3'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testDay()
    {
        $qs = ProductList::objects()->filter(['date_action__day' => 29]);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((EXTRACT(DAY FROM `tests_product_list_1`.`date_action`) = '29'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__day' => '30']);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((EXTRACT(DAY FROM `tests_product_list_1`.`date_action`) = '30'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testWeekDay()
    {
        $qs = ProductList::objects()->filter(['date_action__week_day' => 3]);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((DAYOFWEEK(`tests_product_list_1`.`date_action`) = '3'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__week_day' => '4']);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((DAYOFWEEK(`tests_product_list_1`.`date_action`) = '4'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testHour()
    {
        $qs = ProductList::objects()->filter(['date_action__hour' => 10]);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((EXTRACT(HOUR FROM `tests_product_list_1`.`date_action`) = '10'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__hour' => '11']);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((EXTRACT(HOUR FROM `tests_product_list_1`.`date_action`) = '11'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testMinute()
    {
        $qs = ProductList::objects()->filter(['date_action__minute' => 35]);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((EXTRACT(MINUTE FROM `tests_product_list_1`.`date_action`) = '35'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__minute' => '36']);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((EXTRACT(MINUTE FROM `tests_product_list_1`.`date_action`) = '36'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testSecond()
    {
        $qs = ProductList::objects()->filter(['date_action__second' => 45]);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((EXTRACT(SECOND FROM `tests_product_list_1`.`date_action`) = '45'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__second' => '46']);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((EXTRACT(SECOND FROM `tests_product_list_1`.`date_action`) = '46'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testRegex()
    {
        $qs = ProductList::objects()->filter(['name__regex' => '[a-z]']);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((`tests_product_list_1`.`name` REGEXP BINARY '[a-z]'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['name__regex' => '[0-9]']);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((`tests_product_list_1`.`name` REGEXP BINARY '[0-9]'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testIregex()
    {
        $qs = ProductList::objects()->filter(['name__iregex' => '[P-Z]']);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((`tests_product_list_1`.`name` REGEXP '[P-Z]'))", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['name__iregex' => '[0-9]']);
        $this->assertInstanceOf('\Mindy\Orm\Manager', $qs);
        $this->assertEquals("SELECT COUNT(*) FROM `tests_product_list` `tests_product_list_1` WHERE ((`tests_product_list_1`.`name` REGEXP '[0-9]'))", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }
}
