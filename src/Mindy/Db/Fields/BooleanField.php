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
 * @date 04/01/14.01.2014 02:43
 */

namespace Mindy\Db\Fields;


use Exception;

class BooleanField extends Field
{
    public function __construct(array $options = [])
    {
        if(!isset($options['default'])) {
            throw new Exception("Default param required");
        }
        parent::__construct($options);
    }

    public function sqlType()
    {
        return 'bool';
    }

    public function setValue($value)
    {
        $this->value = (bool) $value;
    }

    public function getValue()
    {
        return (bool) $this->value;
    }
}
