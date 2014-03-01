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
 * @date 04/01/14.01.2014 01:16
 */

namespace Tests\Models;


use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Model;

class ForeignKeyModel extends Model
{
    public function getFields()
    {
        return [
//            'something' => new ForeignField(CreateModel::className(), [
//                    'null' => true
//                ])
            'something' => [
                'class' => ForeignField::className(),
                'modelClass' => CreateModel::className(),
                'null' => true
            ],
        ];
    }
}
