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
 * @date 19/07/14.07.2014 15:58
 */

namespace Mindy\Orm\Exception;


use Exception;

class MultipleObjectsReturned extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        if (empty($message)) {
            $message = "The query returned multiple objects when only one was expected.";
        }
        parent::__construct($message, $code, $previous);
    }
}
