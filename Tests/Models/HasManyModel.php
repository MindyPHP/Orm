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
 * @date 27/02/14.02.2014 15:07
 */

namespace Tests\Models;


use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Model;

class FkModel extends Model
{
    public function getFields()
    {
        return [
//            'fk' => new ForeignField(HasManyModel::className(), [
//                    'null' => true
//                ])
            'fk' => [
                'class' => ForeignField::className(),
                'modelClass' => HasManyModel::className(),
                'null' => true
            ]
        ];
    }
}


class HasManyModel extends Model
{
    public function getFields()
    {
        return [
//            'many' => new HasManyField(FkModel::className(), [
//                    'null' => true
//                ])
            'many' => [
                'class' => HasManyField::className(),
                'modelClass' => FkModel::className(),
                'null' => true
            ]
        ];
    }
}
