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
 * @date 21/05/14.05.2014 18:13
 */

namespace Tests\Orm;

use Mindy\Tests\DatabaseTestCase;
use Tests\Models\User;

class RefacrotingTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initModels([new User]);
    }

    public function testSimple()
    {
        $user = new User();
        $this->assertTrue($user->getIsNewRecord());

        $user->username = '123';
        $user->password = '123';

        $this->assertEquals([
            'username' => '123',
            'password' => '123'
        ], $user->getDirtyAttributes());

        $saved = $user->save();
        $this->assertTrue($saved);

        $this->assertFalse($user->getIsNewRecord());
    }
}
