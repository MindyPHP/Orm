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
 * @date 04/01/14.01.2014 02:39
 */

namespace Tests\Models;


use Mindy\Orm\Fields\BooleanField;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Model;

class SaveUpdateModel extends Model
{
    public function getFields()
    {
        return [
            'name' => new CharField(),
            'price' => new IntField(),
            'is_default' => new BooleanField(['default' => true])
        ];
    }
}
