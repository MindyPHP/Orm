<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Manager;

class CustomManager extends Manager
{
    public function published()
    {
        // do something like this
        // $this->getQuerySet()->filter(['published' => 1]);
        return $this;
    }
}
