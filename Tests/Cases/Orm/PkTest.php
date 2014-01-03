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


use Tests\DatabaseTestCase;
use Tests\Models\PkModel;


class PkTest extends DatabaseTestCase
{
    public function testPk()
    {
        $model = new PkModel();
        $fields = $model->getFieldsInit();

        $this->assertInstanceOf($model->autoField, $fields['id']);
        $this->assertNull($fields['id']->value);
        $this->assertNull($model->id);
        $this->assertNull($model->pk);
    }
}
