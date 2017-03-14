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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DateTimeField.
 */
class DateTimeField extends DateField
{
    /**
     * {@inheritdoc}
     */
    public function getSqlType()
    {
        return Type::getType(Type::DATETIME);
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationConstraints()
    {
        $constraints = [
            new Assert\DateTime(),
        ];
        if ($this->isRequired()) {
            $constraints[] = new Assert\NotBlank();
        }

        return $constraints;
    }
}
