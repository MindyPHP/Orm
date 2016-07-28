<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 28/07/16
 * Time: 13:18
 */

namespace Mindy\Orm;

class DummyObject
{
    public function __call($name, $arguments)
    {
        return null;
    }
}