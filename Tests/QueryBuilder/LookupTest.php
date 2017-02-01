<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests\QueryBuilder;

use Mindy\Orm\Tests\Models\Category;
use Mindy\Orm\Tests\Models\Customer;
use Mindy\Orm\Tests\Models\Order;
use Mindy\Orm\Tests\Models\Product;
use Mindy\Orm\Tests\Models\ProductList;
use Mindy\Orm\Tests\Models\User;
use Mindy\Orm\Tests\OrmDatabaseTestCase;
use Mindy\QueryBuilder\Q\QAnd;
use Mindy\QueryBuilder\Q\QOr;

abstract class LookupTest extends OrmDatabaseTestCase
{
    public function getModels()
    {
        return [
            new Order(),
            new User(),
            new Customer(),
            new Product(),
            new Category(),
            new ProductList(),
        ];
    }

    public function testExact()
    {
        $this->assertTrue((new Product())->save());
        $this->assertEquals(1, Product::objects()->count());
        $qs = Product::objects()->filter(['id' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]]=1)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testIsNull()
    {
        $qs = Product::objects()->filter(['id__isnull' => true]);
        $this->assertEquals(0, $qs->count());
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]] IS NULL)', $qs->countSql());
    }

    public function testIn()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__in' => [1, 2, 3, 4, 5]]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]] IN (1, 2, 3, 4, 5))', $qs->countSql());
        $this->assertEquals(5, $qs->count());
    }

    public function testGte()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__gte' => 2]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]]>=2)', $qs->countSql());
        $this->assertEquals(4, $qs->count());
    }

    public function testGt()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__gt' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]]>1)', $qs->countSql());
        $this->assertEquals(4, $qs->count());
    }

    public function testLte()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__lte' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]]<=1)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testLt()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__lt' => 2]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]]<2)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testContains()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__contains' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]] LIKE @%1%@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testIcontains()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__icontains' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE (LOWER([[product_1]].[[id]]) LIKE @%1%@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testStartswith()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__startswith' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]] LIKE @1%@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testIstartswith()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__istartswith' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE (LOWER([[product_1]].[[id]]) LIKE @1%@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testEndswith()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__endswith' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ([[product_1]].[[id]] LIKE @%1@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testIendswith()
    {
        foreach (range(1, 5) as $i) {
            $model = new Product(['name' => 'name_'.$i]);
            $model->save();
        }
        $qs = Product::objects()->filter(['id__iendswith' => 1]);
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE (LOWER([[product_1]].[[id]]) LIKE @%1@)', $qs->countSql());
        $this->assertEquals(1, $qs->count());
    }

    public function testYear()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-03-29 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__year' => 2014]);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%Y', [[product_list_1]].[[date_action]])=@2014@)", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__year' => '2012']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%Y', [[product_list_1]].[[date_action]])=@2012@)", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testMonth()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-01-29 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__month' => 4]);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%m', [[product_list_1]].[[date_action]])=@04@)", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__month' => '3']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%m', [[product_list_1]].[[date_action]])=@03@)", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testDay()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-02-28 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__day' => 29]);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%d', [[product_list_1]].[[date_action]])=@29@)", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__day' => '30']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%d', [[product_list_1]].[[date_action]])=@30@)", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testWeekDay()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 10:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-02-28 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__week_day' => 1]);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%w', [[product_list_1]].[[date_action]])=@2@)", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__week_day' => '5']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%w', [[product_list_1]].[[date_action]])=@6@)", $qs->countSql());
        $this->assertEquals(0, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__week_day' => '6']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%w', [[product_list_1]].[[date_action]])=@1@)", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testHour()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 12:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-02-28 10:35:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__hour' => 10]);
        $this->assertEquals(1, $qs->count());
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%H', [[product_list_1]].[[date_action]])=@10@)", $qs->countSql());

        $qs = ProductList::objects()->filter(['date_action__hour' => '11']);
        $this->assertEquals(0, $qs->count());
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%H', [[product_list_1]].[[date_action]])=@11@)", $qs->countSql());
    }

    public function testMinute()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 12:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-02-28 10:37:45']))->save());

        $qs = ProductList::objects()->filter(['date_action__minute' => 35]);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%M', [[product_list_1]].[[date_action]])=@35@)", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__minute' => '36']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%M', [[product_list_1]].[[date_action]])=@36@)", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testSecond()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 12:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-02-28 10:37:43']))->save());

        $qs = ProductList::objects()->filter(['date_action__second' => 45]);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%S', [[product_list_1]].[[date_action]])=@45@)", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['date_action__second' => '46']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE (strftime('%S', [[product_list_1]].[[date_action]])=@46@)", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testRange()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 12:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => 'bar', 'date_action' => '2013-02-28 10:37:43']))->save());

        $qs = ProductList::objects()->filter(['id__range' => [0, 1]]);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE ([[product_list_1]].[[id]] BETWEEN 0 AND 1)', $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['id__range' => [10, 20]]);
        $this->assertSql('SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE ([[product_list_1]].[[id]] BETWEEN 10 AND 20)', $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testRegex()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 12:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => '123', 'date_action' => '2013-02-28 10:37:43']))->save());

        $qs = ProductList::objects()->filter(['name__regex' => 'foo']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE ([[product_list_1]].[[name]] REGEXP '/foo/')", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['name__regex' => 'foo123']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE ([[product_list_1]].[[name]] REGEXP '/foo123/')", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testIregex()
    {
        $this->assertTrue((new ProductList(['name' => 'foo', 'date_action' => '2014-04-29 12:35:45']))->save());
        $this->assertTrue((new ProductList(['name' => '123', 'date_action' => '2013-02-28 10:37:43']))->save());

        $qs = ProductList::objects()->filter(['name__iregex' => 'foo']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE ([[product_list_1]].[[name]] REGEXP '/foo/i')", $qs->countSql());
        $this->assertEquals(1, $qs->count());

        $qs = ProductList::objects()->filter(['name__iregex' => 'foo123']);
        $this->assertSql("SELECT COUNT(*) FROM [[product_list]] AS [[product_list_1]] WHERE ([[product_list_1]].[[name]] REGEXP '/foo123/i')", $qs->countSql());
        $this->assertEquals(0, $qs->count());
    }

    public function testSql()
    {
        $qs = Product::objects()
            ->filter(['name' => 'vasya', 'id__lte' => 7])
            ->filter(['name' => 'petya', 'id__gte' => 3]);
        $this->assertSql("SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ((([[product_1]].[[name]]='vasya') AND ([[product_1]].[[id]]<=7))) AND ((([[product_1]].[[name]]='petya') AND ([[product_1]].[[id]]>=3)))", $qs->countSql());

        $qs = Product::objects()
            ->filter(['name' => 'vasya', 'id__lte' => 2])
            ->orFilter(['name' => 'petya', 'id__gte' => 4]);
        $this->assertSql("SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE ((([[product_1]].[[name]]='vasya') AND ([[product_1]].[[id]]<=2))) OR ((([[product_1]].[[name]]='petya') AND ([[product_1]].[[id]]>=4)))", $qs->countSql());
    }

    public function testAllSql()
    {
        $qs = Product::objects()->filter(['id' => 1]);
        $this->assertSql('SELECT [[product_1]].* FROM [[product]] AS [[product_1]] WHERE (([[product_1]].[[id]]=1))', $qs->getSql());
        $this->assertSql('SELECT [[product_1]].* FROM [[product]] AS [[product_1]] WHERE (([[product_1]].[[id]]=1))', $qs->allSql());
        $this->assertSql('SELECT COUNT(*) FROM [[product]] AS [[product_1]] WHERE (([[product_1]].[[id]]=1))', $qs->countSql());
    }

    public function testQ()
    {
        $qs = Product::objects()->filter(new QOr([
            ['name' => 'vasya', 'id__lte' => 7],
            ['name' => 'petya', 'id__gte' => 4],
        ]));
        $this->assertSql(
            "WHERE (([[product_1]].[[name]]='vasya' OR [[product_1]].[[id]]<=7) OR ([[product_1]].[[name]]='petya' OR [[product_1]].[[id]]>=4))",
            $qs->getQueryBuilder()->buildWhere()
        );

        $qs = Product::objects()->filter([
            new QOr([
                ['name' => 'vasya', 'id__lte' => 7],
                ['name' => 'petya', 'id__gte' => 4],
            ]),
            'price__gte' => 200,
        ]);
        $this->assertSql(
            "WHERE ((([[product_1]].[[name]]='vasya' OR [[product_1]].[[id]]<=7) OR ([[product_1]].[[name]]='petya' OR [[product_1]].[[id]]>=4)) AND ([[product_1]].[[price]]>=200))",
            $qs->getQueryBuilder()->buildWhere()
        );

        $qs = Product::objects()->filter([
            new QAnd([
                ['name' => 'vasya', 'id__lte' => 7],
                ['name' => 'petya', 'id__gte' => 4],
            ]),
            'price__gte' => 200,
        ]);

        $this->assertSql(
            "WHERE ((([[product_1]].[[name]]='vasya' AND [[product_1]].[[id]]<=7) AND ([[product_1]].[[name]]='petya' AND [[product_1]].[[id]]>=4)) AND ([[product_1]].[[price]]>=200))",
            $qs->getQueryBuilder()->buildWhere()
        );
    }
}
