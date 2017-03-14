<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
