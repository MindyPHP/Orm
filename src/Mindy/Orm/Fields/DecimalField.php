<?php

namespace Mindy\Orm\Fields;


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
        if ($this->null and is_null($value)) {
            return $this->value = $value;
        } else {
            return $this->value = round($value, $this->scale);
        }
    }
}
