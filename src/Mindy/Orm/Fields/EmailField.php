<?php
/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 03/01/14.01.2014 22:00
 */

namespace Mindy\Orm\Fields;

use Mindy\Validation\EmailValidator;

class EmailField extends CharField
{
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->validators = array_merge([
            new EmailValidator()
        ], $this->validators);
    }
}
