<?php

namespace Mindy\Orm\Fields;

use Mindy\Validation\IpValidator;

/**
 * Class IpField
 * @package Mindy\Orm
 */
class IpField extends CharField
{
    public $version = 4;

    public function init()
    {
        $this->validators[] = new IpValidator($this->version);
    }
}
