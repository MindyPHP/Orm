<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 25/02/15 17:20
 */

namespace Tests\Cases\Orm\Issues;

use stdClass;
use Modules\Tests\Models\Session;
use Tests\OrmDatabaseTestCase;

class IssueCustomPkTest extends OrmDatabaseTestCase
{
    public $driver = 'sqlite';

    protected function getModels()
    {
        return [new Session];
    }

    public function testIssueCustomPk()
    {
        $id = md5(123);
        $expire = time();
        $data = serialize(new StdClass);

        $session = new Session([
            'id' => md5(123),
            'expire' => time(),
            'data' => $data
        ]);

        $this->assertEquals($id, $session->id);
        $this->assertEquals($expire, $session->expire);
        $this->assertEquals($data, $session->data);

        $session->save();

        $session = Session::objects()->get();
        $this->assertEquals($id, $session->id);
        $this->assertEquals($expire, $session->expire);
        $this->assertEquals($data, $session->data);
    }
}
