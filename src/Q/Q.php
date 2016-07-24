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
 * @date 23/06/15 14:39
 */

namespace Mindy\Orm\Q;

use Exception;
use Mindy\Helper\Traits\Accessors;

abstract class Q
{
    use Accessors;

    protected $_conditions = [];

    public function __construct(array $conditions = [])
    {
        foreach($conditions as $condition) {
            if (!is_array($condition)) {
                throw new Exception('Conditions must be arrays');
            }
            $this->_conditions[] = $condition;
        }
    }

    public function getConditions()
    {
        return $this->_conditions;
    }

    abstract public function getQueryCondition();

    abstract public function getQueryJoinCondition();
}