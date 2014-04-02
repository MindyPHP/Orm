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
 * @date 03/01/14.01.2014 22:02
 */

namespace Mindy\Orm\Fields;

use \Mindy\Exception\Exception;

abstract class RelatedField extends IntField
{
    /**
     * @var string
     */
    public $relatedName;

    public function getJoin(){
        throw new Exception('Not implemented');
    }
}
