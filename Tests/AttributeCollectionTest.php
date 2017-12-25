<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests;

use Mindy\Orm\AttributeCollection;
use PHPUnit\Framework\TestCase;

class AttributeCollectionTest extends TestCase
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

    public function testBasic()
    {
        $user = new AttributeCollection();

        $user->setAttribute('pk', 123);
        $this->assertSame(123, $user->getAttribute('pk'));
        $this->assertSame(['pk' => 123], $user->getAttributes());
        $this->assertTrue($user->hasAttribute('pk'));
        $this->assertTrue($user->offsetExists('pk'));
        $this->assertTrue($user->__isset('pk'));
        
        unset($user['pk']);
        $this->assertNull($user['pk']);

        $user['pk'] = 321;
        $this->assertSame(321, $user['pk']);

        $this->assertCount(1, $user);
        $this->assertSame(1, $user->count());
    }
}
