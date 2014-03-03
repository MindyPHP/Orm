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
 * @date 04/03/14.03.2014 01:15
 */

namespace Tests\Models;


use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Model;

class User extends Model
{
    public function getFields()
    {
        return [
            'username' => ['class' => CharField::className()],
            'password' => ['class' => CharField::className()]
        ];
    }
}
