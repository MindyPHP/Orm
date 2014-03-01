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
 * @date 03/01/14.01.2014 22:46
 */

namespace Tests\Models;


use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Model;

class CreateModel extends Model
{
    public function getFields()
    {
        return [
            // 'name' => new CharField(['null' => false]),
            'name' => ['class' => CharField::className(), 'null' => false],
        ];
    }
}
