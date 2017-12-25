<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests;

use Mindy\Orm\Utils\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testBasic()
    {
        $c = new Collection();

        $this->assertSame(0, $c->count());
        $this->assertCount(0, $c);

        $this->assertNull($c->get('key'));

        $c->set('key', 'value');
        $this->assertSame('value', $c->get('key'));
        $this->assertSame('value', $c['key']);
        
        unset($c['key']);
        $this->assertNull($c->get('key'));

        $b = new Collection(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $b->all());

        $c->set('key', 'value');
        $this->assertSame(['key' => 'value', 'foo' => 'bar'], $c->diff($b));
    }
}
