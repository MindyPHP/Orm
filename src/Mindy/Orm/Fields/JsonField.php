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

use Mindy\Orm\Validator\JsonValidator;

class JsonField extends TextField
{
    public function init()
    {
        $this->validators = array_merge([new JsonValidator], $this->validators);
    }

    public function getDbPrepValue()
    {
        return json_encode($this->value, true);
    }
}
