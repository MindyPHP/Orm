<?php

namespace Mindy\Orm\Fields;

use Mindy\Validation\EmailValidator;

/**
 * Class EmailField
 * @package Mindy\Orm
 */
class EmailField extends CharField
{
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->validators = array_merge([
            new EmailValidator(!$this->canBeEmpty())
        ], $this->validators);
    }
}
