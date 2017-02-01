<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Base;

class DummyModel extends Base
{
    public function update(array $fields = [])
    {
        $state = true;
        if ($state) {
            $this->attributes->resetOldAttributes();
        }

        return $state;
    }

    public function insert(array $fields = [])
    {
        $state = true;
        if ($state) {
            $this->attributes->resetOldAttributes();
        }

        return $state;
    }
}
