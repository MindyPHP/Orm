<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Databases\Mysql;

use Mindy\Orm\Tests\Models\ProductList;
use Mindy\Orm\Tests\QueryBuilder\LookupTest;

class MysqlLookupTest extends LookupTest
{
    public $driver = 'mysql';

    public function testYear()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-03-29 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__year' => 2014]);
        $this->assertSql(
            'SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(YEAR FROM [[product_list_1]].[[date_action]])=@2014@)',
            $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__year' => '2012']);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(YEAR FROM [[product_list_1]].[[date_action]])=@2012@)', $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testMonth()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-28 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-02-28 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__month' => 4]);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(MONTH FROM [[product_list_1]].[[date_action]])=@4@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__month' => '3']);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(MONTH FROM [[product_list_1]].[[date_action]])=@3@)', $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testDay()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-03-28 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__day' => 29]);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(DAY FROM [[product_list_1]].[[date_action]])=@29@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__day' => '30']);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(DAY FROM [[product_list_1]].[[date_action]])=@30@)', $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testWeekDay()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-03-28 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__week_day' => 3]);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (DAYOFWEEK([[product_list_1]].[[date_action]])=@3@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__week_day' => '4']);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (DAYOFWEEK([[product_list_1]].[[date_action]])=@4@)', $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testHour()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-03-28 9:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__hour' => 10]);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(HOUR FROM [[product_list_1]].[[date_action]])=@10@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__hour' => '11']);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(HOUR FROM [[product_list_1]].[[date_action]])=@11@)', $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testMinute()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-03-28 10:34:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__minute' => 35]);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(MINUTE FROM [[product_list_1]].[[date_action]])=@35@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__minute' => '36']);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(MINUTE FROM [[product_list_1]].[[date_action]])=@36@)', $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testSecond()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:35']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-03-28 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__second' => 45]);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(SECOND FROM [[product_list_1]].[[date_action]])=@45@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__second' => '46']);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(SECOND FROM [[product_list_1]].[[date_action]])=@46@)', $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testRegex()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:35']))->save());
        $this->assertTrue((new ProductList(['name' => '@@@', 'date_action' => '2013-03-28 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['name__regex' => '[a-z]']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (BINARY [[product_list_1]].[[name]] REGEXP '[a-z]')", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['name__regex' => '[0-9]']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (BINARY [[product_list_1]].[[name]] REGEXP '[0-9]')", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testIregex()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:35']))->save());
        $this->assertTrue((new ProductList(['name' => '@@@', 'date_action' => '2013-03-28 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['name__iregex' => '[A-Z]']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE ([[product_list_1]].[[name]] REGEXP '[A-Z]')", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['name__iregex' => '[0-9]']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE ([[product_list_1]].[[name]] REGEXP '[0-9]')", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }
}
