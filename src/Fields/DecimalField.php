<?php

namespace Mindy\Orm\Fields;
use Doctrine\DBAL\Types\Type;

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

    /**
     * @return string
     */
    public function getSqlType()
    {
        return Type::getType(Type::DECIMAL);
    }

    /**
     * @return array
     */
    public function getSqlOptions() : array
    {
        return array_merge(parent::getSqlOptions(), [
            'precision' => $this->precision,
            'scale' => $this->scale
        ]);
    }

    /**
     * @param $value
     * @return float
     */
    public function setValue($value)
    {
        if (is_null($value)) {
            return $this->value = $value;
        } else {
            return $this->value = round($value, $this->scale);
        }
    }
}
