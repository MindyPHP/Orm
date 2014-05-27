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
 * @date 27/05/14.05.2014 16:43
 */

namespace Mindy\Orm\Fields;


use Mindy\Helper\Meta;

class AutoSlugField extends CharField
{
    public $parent;

    public $source;

    public function getDbPrepValue()
    {
        if($this->parent !== null) {
            return $this->getRecursiveValue();
        } else {
            return $this->getValue();
        }
    }

    public function getValue()
    {
        return Meta::cleanString($this->value);
    }

    public function getRecursiveValue()
    {
        $slugs = [];
        $parent = $this->getModel();
        while(($parent = $parent->{$this->parent}) !== null) {
            $slugs[] = $parent->{$this->name}->getValue();
        }
        return implode('/', $slugs);
    }
}
