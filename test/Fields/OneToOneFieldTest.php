<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 07:34
 */

namespace Mindy\Orm\Tests\Fields;

use Modules\Tests\Models\Place;
use Modules\Tests\Models\Restaurant;
use Tests\OrmDatabaseTestCase;

abstract class OneToOneFieldTest extends OrmDatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->markTestSkipped('TODO');
    }

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

    /**
     * @expectedException \Exception
     */
    public function testOneToOneException()
    {
        $place = new Place();
        $place->name = 'Derry';
        $place->save();

        $restaurant = new Restaurant();
        $restaurant->name = 'Burger mix';
        $restaurant->place = $place;
        $restaurant->save();

        $restaurant2 = new Restaurant();
        $restaurant2->name = 'Cat Burger';
        $restaurant2->place = $place;
        $restaurant2->save();
    }

    public function testOneToOneReverseException()
    {
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
}