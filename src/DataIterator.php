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
}
