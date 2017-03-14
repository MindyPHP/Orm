<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Types\Type;

/**
 * Class BooleanField.
 */
class BooleanField extends Field
{
    /**
     * @var bool
     */
    public $default = false;

    /**
     * @return array
     */
    public function getValidationConstraints()
    {
        return $this->validators;
    }

    /**
     * @return string
     */
    public function getSqlType()
    {
        return Type::getType(Type::BOOLEAN);
    }

    /**
     * @return array
     */
    public function getSqlOptions()
    {
        return array_merge(parent::getSqlOptions(), [
            'default' => $this->default,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        parent::setValue((bool) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return (bool) parent::getValue();
    }
}
