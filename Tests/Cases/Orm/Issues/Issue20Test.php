<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 14/02/15 15:15
 */

namespace Tests\Cases\Orm\Issues;

use Modules\Tests\Models\Customer;
use Modules\Tests\Models\ModelTyre;
use Modules\Tests\Models\Tyre;
use Modules\Tests\Models\User;
use Tests\OrmDatabaseTestCase;

class Issue20Test extends OrmDatabaseTestCase
{
    public $driver = 'sqlite';

    protected function getModels()
    {
        return [new User, new Customer, new Tyre, new ModelTyre];
    }

    public function setUp()
    {
        parent::setUp();

        $user = new User([
            'username' => 'foo'
        ]);
        $user->save();

        (new User([
            'username' => 'bar'
        ]))->save();

        (new Customer([
            'user' => $user,
            'address' => 'address'
        ]))->save();

        $tyre = new Tyre();
        $tyre->save();

        $modelTyre = new ModelTyre([
            'tyre' => $tyre,
            'name' => 'Nordman 4'
        ]);
        $modelTyre->save();

        $modelTyre = new ModelTyre([
            'tyre' => $tyre,
            'name' => 'Nordman 3'
        ]);
        $modelTyre->save();

        $modelTyre = new ModelTyre([
            'tyre' => $tyre,
            'name' => 'Nordman 2'
        ]);
        $modelTyre->save();
    }

    public function testIssue20_2()
    {
        $filter = ['model_tyre__name' => 'Nordman 4'];
        $qs = Tyre::objects()->getQuerySet()->filter($filter);
        $data = $qs->with(['model_tyre'])->asArray()->all();
        $this->assertEquals([
            [
                'model_tyre' => [
                    'id' => 1,
                    'name' => 'Nordman 4',
                    'tyre_id' => 1
                ],
                'id' => 1,
                'tyre_id' => 1
            ]
        ], $data);
    }

    public function testIssue20()
    {
        $qs = User::objects()->with(['addresses'])->asArray();
        $this->assertEquals([
            [
                'addresses' => [
                    'id' => 1,
                    'user_id' => 1,
                    'address' => 'address'
                ],
                'id' => 1,
                'username' => 'foo',
                'password' => '',
                'user_id' => 1
            ],
            [
                'addresses' => [
                    'id' => null,
                    'user_id' => null,
                    'address' => ''
                ],
                'id' => 2,
                'username' => 'bar',
                'password' => '',
                'user_id' => ''
            ]
        ], $qs->all());

        $qs = User::objects()->filter(['addresses__address' => 'address'])->with(['addresses'])->asArray();
        $this->assertEquals([
            [
                'addresses' => [
                    'id' => 1,
                    'user_id' => 1,
                    'address' => 'address'
                ],
                'id' => 1,
                'username' => 'foo',
                'password' => '',
                'user_id' => 1
            ]
        ], $qs->all());
    }
}
