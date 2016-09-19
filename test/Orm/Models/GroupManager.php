<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 13:12
 */

namespace Mindy\Tests\Orm\Models;

use Mindy\Orm\Manager;

class GroupManager extends Manager
{
    public function published()
    {
        return $this;
    }
}