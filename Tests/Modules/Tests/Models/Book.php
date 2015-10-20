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
 * @date 15/07/14.07.2014 17:40
 */

namespace Modules\Tests\Models;

use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Model;

class Book extends Model
{
    public static function getFields()
    {
        return [
            'category' => [
                'class' => ForeignField::className(),
                'modelClass' => BookCategory::className(),
                'null' => true,
                'editable' => false
            ],
            'category_new' => [
                'class' => ForeignField::className(),
                'modelClass' => BookCategory::className(),
                'null' => true,
                'relatedName' => 'categories',
                'editable' => false
            ]
        ];
    }
}
