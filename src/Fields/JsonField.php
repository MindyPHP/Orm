<?php

namespace Mindy\Orm\Fields;

use Mindy\Helper\Json;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class JsonField
 * @package Mindy\Orm
 */
class JsonField extends TextField
{
    /**
     * @return array
     */
    public function getValidationConstraints() : array
    {
        return array_merge(parent::getValidationConstraints(), [
            new Assert\Callback(function ($value, ExecutionContextInterface $context, $payload) {
                if (
                    is_object($value) &&
                    method_exists($value, 'toJson') === false &&
                    method_exists($value, 'toArray') === false
                ) {
                    $context->addViolation('Not json serialize object: %type%', ['%type%' => gettype($value)]);
                }
            })
        ]);
    }

    private function decode($value)
    {
        if (empty($value)) {
            $value = '{}';
        }
        $this->value = is_string($value) ? Json::decode($value) : $value;
        return $this->value;
    }

    public function getValue()
    {
        return $this->decode($this->value);
    }

    public function setDbValue($value)
    {
        $this->value = $this->decode($value);
        return $this;
    }

    public function setValue($value)
    {
        return $this->decode($value);
    }

    public function getDbPrepValue()
    {
        return is_string($this->value) ? $this->value : Json::encode($this->value);
    }
}
