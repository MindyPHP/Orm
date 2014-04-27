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
 * @date 04/01/14.01.2014 03:04
 */

namespace Tests\Orm;

use Tests\DatabaseTestCase;
use Tests\Models\User;

class QueryTest extends DatabaseTestCase
{
    public $prefix = '';

    public function setUp()
    {
        parent::setUp();

        $this->initModels([new User]);

        $this->items = [
            ['username' => 'Anton', 'password' => 'VeryGoodPassWord'],
            ['username' => 'Max', 'password' => 'The6estP@$$w0rd'],
        ];
        foreach($this->items as $item) {
            $tmp = new User();
            foreach($item as $name => $value) {
                $tmp->$name = $value;
            }
            $tmp->save();
        }

        $model = new User();
        $this->prefix = $model->getConnection()->tablePrefix;
    }

    public function testFind()
    {
        $qs = User::objects();
        $this->assertEquals(2, $qs->count());
        $this->assertEquals([
            [
                'id' => 1,
                'username' => 'Anton',
                'password' => 'VeryGoodPassWord'
            ],
            [
                'id' => 2,
                'username' => 'Max',
                'password' => 'The6estP@$$w0rd'
            ]
        ], $qs->all(true));
    }

    public function testFindWhere()
    {
        $qs = User::objects();
        $this->assertEquals(1, $qs->filter(['username' => 'Max'])->count());
        $this->assertEquals([
            [
                'id' => 2,
                'username' => 'Max',
                'password' => 'The6estP@$$w0rd'
            ]
        ], $qs->all(true));
    }

    public function testExclude()
    {
        $qs = User::objects()->filter(['username' => 'Anton'])->exclude(['username' => 'Max']);
        $this->assertEquals(1, $qs->count());
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}user` WHERE ((`username`='Anton')) AND (NOT ((`username`='Max')))", $qs->countSql());
    }

    public function testOrExclude()
    {
        $qs = User::objects()->exclude(['username' => 'Max'])->orExclude(['username' => 'Anton']);
        $this->assertEquals(2, $qs->count());
        $this->assertEquals("SELECT COUNT(*) FROM `{$this->prefix}user` WHERE (NOT ((`username`='Max'))) OR (NOT ((`username`='Anton')))", $qs->countSql());
    }
}
