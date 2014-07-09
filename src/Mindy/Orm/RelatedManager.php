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
 * @date 04/01/14.01.2014 03:42
 */

namespace Mindy\Orm;

class RelatedManager extends Manager
{
    protected function escape($value)
    {
        // if has auto-quotation
        if (strpos($value, '{{') !== false || strpos($value, '`') !== false || strpos($value, '[[') !== false) {
            return $value;
        }
        return '`' . $value . '`';
    }
}
