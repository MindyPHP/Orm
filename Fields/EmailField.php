<?php

namespace Mindy\Orm\Fields;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EmailField
 * @package Mindy\Orm
 */
class EmailField extends CharField
{
    /**
     * @var bool
     */
    public $checkMX = false;
    /**
     * @var bool
     */
    public $checkHost = false;

    /**
     * @return array
     */
    public function getValidationConstraints() : array
    {
        return array_merge(parent::getValidationConstraints(), [
            new Assert\Email([
                'checkMX' => $this->checkMX,
                'checkHost' => $this->checkHost
            ])
        ]);
    }
}
