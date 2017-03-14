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
 * Class ObjectDoesNotExist.
 */
class ObjectDoesNotExist extends Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        if (empty($message)) {
            $message = 'The requested object does not exist';
        }
        parent::__construct($message, $code, $previous);
    }
}
