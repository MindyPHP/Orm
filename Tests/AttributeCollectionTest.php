<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 10:56.
 */

namespace Mindy\Orm\Tests;

use Mindy\Orm\AttributeCollection;

class AttributeCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testOldAttributes()
    {
        $user = new AttributeCollection();

        $user->username = 'foo';
        $this->assertNull($user->getOldAttribute('username'));

        $user->username = 'bar';
        $this->assertEquals('foo', $user->getOldAttribute('username'));

        $user->resetOldAttributes();

        $this->assertEquals('bar', $user->username);
        $this->assertNull($user->getOldAttribute('username'));
    }

    public function testDirtyAttributes()
    {
        $user = new AttributeCollection();

        $this->assertEquals([], $user->getDirtyAttributes());

        $user->username = 'foo';
        $this->assertEquals(['username'], $user->getDirtyAttributes());

        $user->username = 'bar';
        $this->assertEquals(['username'], $user->getDirtyAttributes());

        $user->password = 'pwd';
        $this->assertEquals(['username', 'password'], $user->getDirtyAttributes());

        $user->resetOldAttributes();

        $this->assertEquals([], $user->getDirtyAttributes());
    }
}
