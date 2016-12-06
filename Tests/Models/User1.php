<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 19:26
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Model;

class User1 extends Model
{
    public static function tableName() : string
    {
        return "{{user1}}";
    }
}