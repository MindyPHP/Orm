<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests\Databases\Pgsql;

use Mindy\Orm\Tests\Models\Product;
use Mindy\Orm\Tests\Models\ProductList;
use Mindy\Orm\Tests\QueryBuilder\LookupTest;

class PgsqlLookupTest extends LookupTest
{
    public $driver = 'pgsql';

    public function tearDown()
    {
    }

    public function testYear()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2012-03-29 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__year' => 2014]);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(YEAR FROM [[product_list_1]].[[date_action]]::timestamp)=@2014@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__year' => '2013']);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(YEAR FROM [[product_list_1]].[[date_action]]::timestamp)=@2013@)', $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testMonth()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2012-02-29 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__month' => 4]);
        $this->assertEquals(1, $qs->count());
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(MONTH FROM [[product_list_1]].[[date_action]]::timestamp)=@4@)', $qs->countSql());

        $qs = ProductList::objects()->filter(['date_action__month' => '3']);
        $this->assertEquals(0, $qs->count());
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(MONTH FROM [[product_list_1]].[[date_action]]::timestamp)=@3@)', $qs->countSql());
    }

    public function testDay()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2012-03-28 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__day' => 29]);
        $this->assertEquals(1, $qs->count());
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(DAY FROM [[product_list_1]].[[date_action]]::timestamp)=@29@)', $qs->countSql());

        $qs = ProductList::objects()->filter(['date_action__day' => '30']);
        $this->assertEquals(0, $qs->count());
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(DAY FROM [[product_list_1]].[[date_action]]::timestamp)=@30@)', $qs->countSql());
    }

    public function testWeekDay()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2012-03-29 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__week_day' => 2]);
        $this->assertEquals(1, $qs->count());
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(DOW FROM [[product_list_1]].[[date_action]]::timestamp)=@2@)', $qs->countSql());

        $qs = ProductList::objects()->filter(['date_action__week_day' => '3']);
        $this->assertEquals(0, $qs->count());
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(DOW FROM [[product_list_1]].[[date_action]]::timestamp)=@3@)', $qs->countSql());
    }

    public function testHour()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2012-03-29 9:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__hour' => 10]);
        $this->assertEquals(1, $qs->count());
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(HOUR FROM [[product_list_1]].[[date_action]]::timestamp)=@10@)', $qs->countSql());

        $qs = ProductList::objects()->filter(['date_action__hour' => '11']);
        $this->assertEquals(0, $qs->count());
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(HOUR FROM [[product_list_1]].[[date_action]]::timestamp)=@11@)', $qs->countSql());
    }

    public function testMinute()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2012-03-29 10:34:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__minute' => 35]);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(MINUTE FROM [[product_list_1]].[[date_action]]::timestamp)=@35@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__minute' => '36']);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(MINUTE FROM [[product_list_1]].[[date_action]]::timestamp)=@36@)', $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testSecond()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2012-03-29 10:35:35']))->save());

        $qs = ProductList::objects()->filter(['date_action__second' => 45]);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(SECOND FROM [[product_list_1]].[[date_action]]::timestamp)=@45@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__second' => '46']);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (EXTRACT(SECOND FROM [[product_list_1]].[[date_action]]::timestamp)=@46@)', $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testRegex()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => '@@@', 'date_action' => '2012-03-29 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['name__regex' => '^foo']);
        $this->assertEquals(1, $qs->count());
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE ([[product_list_1]].[[name]]~'^foo')", $qs->countSql());

        $qs = ProductList::objects()->filter(['name__regex' => '^(b|c)']);
        $this->assertEquals(0, $qs->count());
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE ([[product_list_1]].[[name]]~'^(b|c)')", $qs->countSql());
    }

    public function testIregex()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => '@@@', 'date_action' => '2012-03-29 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['name__iregex' => '^foo']);
        $this->assertEquals(1, $qs->count());
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE ([[product_list_1]].[[name]]~*'^foo')", $qs->countSql());

        $qs = ProductList::objects()->filter(['name__iregex' => '^(b|c)']);
        $this->assertEquals(0, $qs->count());
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE ([[product_list_1]].[[name]]~*'^(b|c)')", $qs->countSql());
    }

    public function testContains()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__contains' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]]::text LIKE @%1%@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testIcontains()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__icontains' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE (LOWER([[product_1]].[[id]]::text) LIKE @%1%@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testStartswith()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__startswith' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]]::text LIKE @1%@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testIstartswith()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__istartswith' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE (LOWER([[product_1]].[[id]]::text) LIKE @1%@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testEndswith()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__endswith' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]]::text LIKE @%1@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testIendswith()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__iendswith' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE (LOWER([[product_1]].[[id]]::text) LIKE @%1@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }
}
