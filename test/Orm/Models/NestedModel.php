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
 * @date 05/05/14.05.2014 18:53
 */

namespace Mindy\Tests\Orm\Models;


use Mindy\Orm\Fields\AutoSlugField;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\TreeModel;

class NestedModel extends TreeModel
{
    public static function getFields()
    {
        return array_merge(parent::getFields(), [
            'name' => [
                'class' => CharField::class
            ]
        ]);
    }
}
