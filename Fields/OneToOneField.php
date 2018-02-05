<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Index;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class OneToOneField.
 */
class OneToOneField extends ForeignField
{
    /**
     * @var string
     */
    public $to;

    public function getSqlType()
    {
        if ($this->primary) {
            return parent::getSqlType();
        }

        return false;
    }

    public function getValue()
    {
        if (false === $this->primary) {
            $model = $this->getModel();
            if ($model->getIsNewRecord()) {
                return;
            }

            return $this->getRelatedModel()->objects()->get(['pk' => $model->pk]);
        }

        return parent::getValue();
    }

    public function getValidationConstraints()
    {
        if ($this->primary) {
            $constraints = [
                new Assert\NotBlank(),
                new Assert\Callback(function ($value, ExecutionContextInterface $context, $payload) {
                    if (empty($value)) {
                        return;
                    }

                    if ($this->getModel()->objects()->filter(['pk' => $value])->count() > 0) {
                        $context->buildViolation('The value must be unique')->addViolation();
                    }

                    if (0 === $this->getRelatedModel()->objects()->filter(['pk' => $value])->count()) {
                        $context->buildViolation('The primary model not found')->addViolation();
                    }
                }),
            ];
        } else {
            $constraints = [];
        }

        return $constraints;
    }

    public function reversedTo()
    {
        return $this->to;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return $value;
        }

        return $this->getRelatedModel()->objects()->get([
            $this->to => $value,
        ]);
    }

    public function getSqlIndexes(): array
    {
        $indexes = [];
        $name = $this->primary ? $this->name.'_id' : $this->name;
        if ($this->primary) {
            $indexes[] = new Index('PRIMARY', [$name], true, true);
        } elseif ($this->unique && !$this->primary) {
            $indexes[] = new Index($name.'_idx', [$name], true, false);
        }

        return $indexes;
    }

    /**
     * @return string
     */
    public function getAttributeName()
    {
        if ($this->primary) {
            $primaryKeyName = call_user_func([$this->modelClass, 'getPrimaryKeyName']);

            return $this->name.'_'.$primaryKeyName;
        }

        return $this->name.'_id';
    }
}
