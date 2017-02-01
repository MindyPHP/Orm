<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Types\Type;

/**
 * Class DecimalField.
 */
class DecimalField extends Field
{
    /**
     * @var int Number of digits in integer or to the left of the decimal point
     */
    public $precision = 10;
    /**
     * @var int Number of digits to the right of the decimal point
     */
    public $scale = 2;

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
    public function getSqlOptions()
    {
        return array_merge(parent::getSqlOptions(), [
            'precision' => $this->precision,
            'scale' => $this->scale,
        ]);
    }

    /**
     * @param $value
     *
     * @return float
     */
    public function setValue($value)
    {
        if (is_null($value)) {
            return $this->value = $value;
        }

        return $this->value = round($value, $this->scale);
    }
}
