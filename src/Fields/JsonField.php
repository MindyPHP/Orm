<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class JsonField
 * @package Mindy\Orm
 */
class JsonField extends TextField
{
    public function getSqlType()
    {
        return Type::getType(Type::JSON_ARRAY);
    }

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

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (is_string($value)) {
            return parent::convertToPHPValue($value, $platform);
        } else {
            return $value;
        }
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return mixed
     */
    public function convertToPHPValueSql($value, AbstractPlatform $platform)
    {
        $opts = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        if (is_object($value)) {
            if (method_exists($value, 'toJson')) {
                $value = $value->toJson();
            } else if (method_exists($value, 'toArray')) {
                $value = json_encode($value->toArray(), $opts);
            } else {
                $value = json_encode($value, $opts);
            }
        }

        return parent::convertToPHPValueSql($value, $platform);
    }
}
