<?php

namespace Mindy\Orm\Exception;

use Exception;

/**
 * Class MultipleObjectsReturned.
 */
class MultipleObjectsReturned extends Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        if (empty($message)) {
            $message = 'The query returned multiple objects when only one was expected.';
        }
        parent::__construct($message, $code, $previous);
    }
}
