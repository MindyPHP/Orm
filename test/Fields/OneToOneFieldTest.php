<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 07:34
 */

namespace Mindy\Orm\Tests\Fields;

use Exception;
use Modules\Tests\Models\Place;
use Modules\Tests\Models\Restaurant;
use Mindy\Orm\Tests\OrmDatabaseTestCase;

abstract class OneToOneFieldTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [
            new Place,
            new Restaurant
        ];
    }

    public function testOneToOneKey()
    {
        $place = new Place();
        $this->assertTrue($place->hasField('id'));
        $this->assertTrue($place->hasField('name'));
        $this->assertTrue($place->hasField('restaurant'));
        $this->assertTrue($place->hasAttribute('restaurant_id'));
        $this->assertEquals('id', $place->primaryKeyName());
        $this->assertTrue($place->hasField('pk'));

        $place->name = 'Derry';
        $this->assertTrue($place->save());
        $this->assertEquals(1, $place->pk);

        $restaurant = new Restaurant();
        $restaurant->name = 'Burger mix';
        $restaurant->place = $place;

        $this->assertTrue($restaurant->hasField('place'));
        $this->assertTrue($restaurant->hasAttribute('place_id'));
        $this->assertEquals(1, $restaurant->getAttribute('place_id'));
        $this->assertTrue($restaurant->hasField('place'));
        $this->assertTrue($restaurant->hasAttribute('place_id'));
        $this->assertEquals(1, $restaurant->getAttribute('place_id'));

        $this->assertTrue($restaurant->save());
        $this->assertEquals(1, $restaurant->getAttribute('place_id'));
        $this->assertEquals(1, $restaurant->place_id);
        $this->assertEquals(1, $restaurant->pk);
        $this->assertEquals(1, $place->pk);
        $this->assertEquals(1, Restaurant::objects()->filter(['place' => $place->id])->count());
        $this->assertEquals(1, $place->restaurant->pk);

        $restaurant->delete();
        $this->assertNull($place->restaurant);
    }

    public function testOneToOne()
    {
        $place = new Place();
        $place->name = 'Derry';
        $place->save();

        $restaurant = new Restaurant();
        $restaurant->name = 'Burger mix';
        $restaurant->place = $place;
        $this->assertTrue($restaurant->isValid());
        $restaurant->save();

        $restaurant2 = new Restaurant();
        $restaurant2->name = 'Cat Burger';
        $restaurant2->place = $place;
        $this->assertFalse($restaurant2->isValid());
        $restaurant2->save();
    }

    public function testOneToOneReverseException()
    {
        $this->setExpectedException(Exception::class, Restaurant::class . ' must have unique key');
        $place = new Place();
        $place->name = 'Derry';
        $place->save();

        $place2 = new Place();
        $place2->name = 'Dallas';
        $place2->save();

        $restaurant = new Restaurant();
        $restaurant->name = 'Burger mix';
        $restaurant->place = $place;
        $restaurant->save();

        $restaurant2 = new Restaurant();
        $restaurant2->name = 'Cat Burger';
        $restaurant2->place = $place2;
        $restaurant2->save();

        $place->restaurant = $restaurant2;
        $place->save();
    }

    public function testOneToOneKeyInt()
    {
        $place = new Place();
        $place->name = 'Derry';
        $place->save();

        $restaurant = new Restaurant();
        $restaurant->name = 'Burger mix';
        $restaurant->place_id = 1;
        $restaurant->save();

        $this->assertEquals(1, Restaurant::objects()->filter(['place' => $place->id])->count());
        $this->assertEquals(1, $place->restaurant->pk);

        $restaurant->delete();

        $this->assertNull($place->restaurant);
    }
}