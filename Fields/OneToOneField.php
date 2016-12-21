<?php

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

    public function init()
    {
        parent::init();

        if ($this->primary) {
            $this->unique = true;
        } else {
            $this->null = true;
        }
    }

    public function getSqlType()
    {
        if ($this->primary) {
            return parent::getSqlType();
        }

        return false;
    }

    public function getValue()
    {
        if ($this->primary === false) {
            $model = $this->getModel();
            if ($model->getIsNewRecord()) {
                return;
            }

            return $this->getRelatedModel()->objects()->get(['pk' => $model->pk]);
        } else {
            return parent::getValue();
        }
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

                    if ($this->getRelatedModel()->objects()->filter(['pk' => $value])->count() === 0) {
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
        if ($value === null) {
            return $value;
        }

        return $this->getRelatedModel()->objects()->get([
            $this->to => $value,
        ]);
    }

    public function getSqlIndexes()
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
        } else {
            return $this->name.'_id';
        }
    }
}
