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
 * @date 03/01/14.01.2014 21:52
 */

namespace Mindy\Orm;

use Mindy\Base\Mindy;
use \Yii;
use Mindy\Orm\Traits\AppYiiCompatible;

class Model extends Orm
{
    use AppYiiCompatible;

    public function __toString()
    {
        return (string) $this->shortClassName();
    }

    /**
     * @deprecated
     * @param array $values
     * @return $this
     */
    public function setData(array $values)
    {
        $this->setAttributes($values);
        return $this;
    }

    public function getVerboseName()
    {
        return $this->shortClassName();
    }

    public function generateUrl($route, $data = null)
    {
        return Mindy::app()->urlManager->createUrl($route, $data);
    }
}
