<?php

namespace Mindy\Orm\Fields;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class IpField
 * @package Mindy\Orm
 */
class IpField extends CharField
{
    /**
     * @var int
     */
    public $version = 4;

    /**
     * @return array
     */
    public function getValidationConstraints() : array
    {
        return array_merge(parent::getValidationConstraints(), [
            new Assert\Ip(['version' => $this->version])
        ]);
    }
}
