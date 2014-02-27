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
 * @date 04/01/14.01.2014 21:19
 */

namespace Tests\Models;


use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\Model;

class ManyModel extends Model
{
    public function getFields()
    {
        return [
            'name' => new CharField(['null' => true]),
            'items' => new ManyToManyField(CreateModel::className())
        ];
    }
}
