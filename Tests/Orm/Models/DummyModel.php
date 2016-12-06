<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 19:43
 */

namespace Mindy\Tests\Orm\Models;

use Mindy\Orm\NewBase;

class DummyModel extends NewBase
{
    public function update(array $fields = []) : bool
    {
        $state = true;
        if ($state) {
            $this->attributes->resetOldAttributes();
        }
        return $state;
    }

    public function insert(array $fields = []) : bool
    {
        $state = true;
        if ($state) {
            $this->attributes->resetOldAttributes();
        }
        return $state;
    }
}