<?php

namespace Mindy\Orm\Fields;

/**
 * Class FloatField
 * @package Mindy\Orm
 */
class FloatField extends Field
{
    public function sqlType()
    {
        return 'float';
    }
}
