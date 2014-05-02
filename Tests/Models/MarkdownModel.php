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
 * @date 02/05/14.05.2014 17:22
 */

namespace Tests\Models;


use Mindy\Orm\Fields\MarkdownField;
use Mindy\Orm\Model;

class MarkdownModel extends Model
{
    public function getFields()
    {
        return [
            'content' => ['class' => MarkdownField::className()],
        ];
    }
}
