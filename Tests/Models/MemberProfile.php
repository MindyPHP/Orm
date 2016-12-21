<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 15:32.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\OneToOneField;
use Mindy\Orm\Model;

class MemberProfile extends Model
{
    public static function getFields()
    {
        return [
            'user' => [
                'class' => OneToOneField::class,
                'modelClass' => Member::class,
                'primary' => true,
                'to' => 'id',
            ],
        ];
    }
}
