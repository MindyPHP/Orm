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

trait AppYiiCompatible
{
    /**
     * @return string
     */
    public function getModuleName()
    {
        return dirname(__DIR__);
    }

    /**
     * @return MWebModule
     */
    public function getModule()
    {
        return Yii::app()->getModule($this->getModuleName());
    }
}
