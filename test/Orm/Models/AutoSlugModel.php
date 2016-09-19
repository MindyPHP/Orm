<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 18/09/16
 * Time: 20:58
 */

namespace Mindy\Tests\Orm\Models;

use Mindy\Orm\Fields\AutoSlugField;

class AutoSlugModel extends NestedModel
{
    public static function getFields()
    {
        return array_merge(parent::getFields(), [
            'slug' => [
                'class' => AutoSlugField::class
            ]
        ]);
    }
}