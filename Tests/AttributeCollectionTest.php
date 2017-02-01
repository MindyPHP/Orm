<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
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
