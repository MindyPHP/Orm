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
 * @date 18/04/14.04.2014 19:29
 */

namespace Mindy\Orm\Traits;

use Mindy\Base\Mindy;
use ReflectionObject;

trait AppYiiCompatible
{
    /**
     * @return string
     */
    public function getModuleName()
    {
        $object = new ReflectionObject($this);
        return strtolower(basename(dirname(dirname($object->getFilename()))));
    }

    /**
     * @return \Mindy\Base\Module
     */
    public function getModule()
    {
        return Mindy::app()->getModule($this->getModuleName());
    }
}
