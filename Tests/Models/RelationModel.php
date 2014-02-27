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
use Mindy\Orm\Model;

class RelationModel extends Model
{
    public function getFields()
    {
        return [
            'fk' => new ForeignField(CreateModel::className(), [
                    'null' => true
                ])
        ];
    }
}


class RelationFkModel extends Model
{
    public function getFields()
    {
        return [];
    }
}
