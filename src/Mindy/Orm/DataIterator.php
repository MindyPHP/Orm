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
 * @date 18/07/14.07.2014 16:54
 */

namespace Mindy\Orm;


use ArrayIterator;
use Mindy\Helper\Traits\Configurator;

/**
 * TODO unused prototype for next refactoring
 * Class DataIterator
 * @package Mindy\Orm
 */
class DataIterator extends ArrayIterator
{
    use Configurator;

    /**
     * @var bool
     */
    public $asArray;
    /**
     * @var QuerySet
     */
    public $qs;

    public function __construct(array $data, array $options = [], $flags = 0)
    {
        parent::__construct($data, $flags);
        $this->configure($options);
    }

    public function offsetGet($offset)
    {
        $item = parent::offsetGet($offset);
        return $this->asArray ? $item : $this->qs->createModel($item);
    }

    public function current()
    {
        $item = parent::current();
        return $this->asArray ? $item : $this->qs->createModel($item);
    }
}
