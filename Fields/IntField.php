<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Class IntField.
 */
class IntField extends Field
{
    /**
     * @var int|string
     */
    public $length = 11;
    /**
     * @var bool
     */
    public $unsigned = false;

    /**
     * @return Type
     */
    public function getSqlType()
    {
        return Type::getType(Type::INTEGER);
    }

    public function getSqlOptions()
    {
        $options = parent::getSqlOptions();
        if ($this->primary) {
            $options['autoincrement'] = true;
        } else {
            $options['unsigned'] = $this->unsigned;
        }

        return $options;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        parent::setValue($this->null ? $value : (int) $value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return;
        }

        return (int) parent::convertToPHPValue($value, $platform);
    }
}
