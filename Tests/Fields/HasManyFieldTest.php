<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Fields;

use Mindy\Orm\Tests\Models\Category;
use Mindy\Orm\Tests\Models\Color;
use Mindy\Orm\Tests\Models\Cup;
use Mindy\Orm\Tests\Models\Design;
use Mindy\Orm\Tests\Models\Product;
use Mindy\Orm\Tests\OrmDatabaseTestCase;
use Mindy\QueryBuilder\QueryBuilder;

abstract class HasManyFieldTest extends OrmDatabaseTestCase
{
    public function getModels()
    {
        return [new Product(), new Category(), new Cup(), new Design(), new Color()];
    }

    public function testSimple()
    {
        $categoryToys = new Category([
            'name' => 'Toys',
        ]);
        $this->assertTrue($categoryToys->getIsNewRecord());
        $categoryToys->save();
        $this->assertFalse($categoryToys->getIsNewRecord());

        $category_animals = new Category();
        $category_animals->name = 'Animals';
        $category_animals->save();

        $connection = $this->getConnection();
        $adapter = QueryBuilder::getInstance($connection)->getAdapter();
        $tableSql = $adapter->quoteColumn('product');
        $tableAliasSql = $adapter->quoteColumn('product_1');
        $categoryIdSql = $adapter->quoteColumn('category_id');

        $this->assertEquals("SELECT COUNT(*) FROM $tableSql AS $tableAliasSql WHERE ($tableAliasSql.$categoryIdSql='1')", $categoryToys->products->countSql());
        $this->assertEquals(0, $categoryToys->products->count());

        $product_bear = new Product([
            'category' => $categoryToys,
            'name' => 'Bear',
            'price' => 100,
            'description' => 'Funny white bear',
        ]);
        $product_bear->save();

        $this->assertEquals(1, $categoryToys->products->count());

        $product_rabbit = new Product([
            'category' => $category_animals,
            'name' => 'Rabbit',
            'price' => 110,
            'description' => 'Rabbit with carrot',
        ]);
        $product_rabbit->save();

        $this->assertEquals(1, $categoryToys->products->count());

        $product_rabbit->category = $categoryToys;
        $product_rabbit->save();

        $this->assertEquals(2, $categoryToys->products->count());
    }

    public function testThrough()
    {
    }

    public function testMultiple()
    {
        $cup = new Cup();
        $cup->name = 'Amazing cup';
        $cup->save();

        $design = new Design();
        $design->name = 'Dragon';
        $design->cup = $cup;
        $design->save();

        $color = new Color();
        $color->name = 'red';
        $color->cup = $cup;
        $color->save();

        $qs = Cup::objects()->filter(['designs__name' => 'Dragon', 'colors__name' => 'red']);
        $sql = $qs->allSql();
        $this->assertSql("SELECT [[cup_1]].* FROM [[cup]] AS [[cup_1]] LEFT JOIN [[design]] AS [[design_1]] ON [[design_1]].[[cup_id]]=[[cup_1]].[[id]] LEFT JOIN [[color]] AS [[color_1]] ON [[color_1]].[[cup_id]]=[[cup_1]].[[id]] WHERE (([[design_1]].[[name]]='Dragon') AND ([[color_1]].[[name]]='red'))", $sql);
        $this->assertEquals(1, $qs->count());
    }
}
