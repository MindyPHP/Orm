<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 04/01/14.01.2014 00:53
 */

namespace Tests\Orm;


use Mindy\Orm\LookupBuilder;
use Tests\DatabaseTestCase;


class LookupTest extends DatabaseTestCase
{
    public function testIn()
    {
        $query = ['items__user__pages__pk__in' => [1, 2, 3]];
        $lookup = new LookupBuilder($query);
        $this->assertEquals([
            [
                ['items', 'user', 'pages'],
                'pk',
                'in',
                [1, 2, 3]
            ]
        ], $lookup->parse());
    }

    public function testSimple()
    {
        $query = ['items__user__pages__pk' => 1];
        $lookup = new LookupBuilder($query);
        $this->assertEquals([
            [
                ['items', 'user', 'pages'],
                'pk',
                'exact',
                1
            ]
        ], $lookup->parse());
    }
}
