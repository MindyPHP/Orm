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
    public $source;

    public function getDbPrepValue()
    {
        return $this->getRecursiveValue();
    }

    public function getValue()
    {
        return $this->getRecursiveValue();
    }

    public function getRecursiveValue()
    {
        $slugs = [];
        $parent = $this->getModel();
        $slugs[] = Meta::cleanString($parent->{$this->source});
        while(($parent = $parent->parent) !== null) {
            $slugs[] = $parent->{$this->source};
        }
        return implode('/', array_reverse($slugs));
    }
}
