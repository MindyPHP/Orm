<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 11/12/14 20:13
 */

namespace Mindy\Orm\Fields;

use Mindy\Validation\IpValidator;

class IpField extends CharField
{
    public $version = 4;

    public function init()
    {
        $this->validators[] = new IpValidator($this->version);
    }
}
