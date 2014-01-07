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
 * @date 03/01/14.01.2014 22:28
 */

namespace Tests\Models;


use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Model;

class ModelFields extends Model
{
    public function getFields()
    {
        return [
            'name' => new CharField()
        ];
    }
}
