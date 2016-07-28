<?php

namespace Mindy\Orm\Fields;

/**
 * Class DecimalField
 * @package Mindy\Orm
 */
class DecimalField extends Field
{
    /**
     * number of digits in integer or to the left of the decimal point
     * @var int
     */
    public $precision;

    /**
     * @var int number of digits to the right of the decimal point
     */
    public $scale;

    public function sqlType()
    {
        return sprintf('decimal(%d, %d)', $this->precision, $this->scale);
    }

    public function setValue($value)
    {
        if (is_null($value)) {
            return $this->value = $value;
        } else {
            return $this->value = round($value, $this->scale);
        }
    }
}
