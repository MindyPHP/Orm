<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Okulov Anton
 * @email qantus@mail.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 23/06/15 14:48
 */

namespace Mindy\Orm\Q;


class AndQ extends Q
{
    public function getQueryCondition()
    {
        return ['and'];
    }

    public function getQueryJoinCondition()
    {
        return ['and'];
    }
}