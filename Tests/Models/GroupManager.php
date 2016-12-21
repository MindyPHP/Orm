<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 24/07/16
 * Time: 13:12.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Manager;

class GroupManager extends Manager
{
    public function published()
    {
        return $this;
    }
}
