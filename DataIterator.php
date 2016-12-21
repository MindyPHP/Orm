<?php
/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 *
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 18/07/14.07.2014 16:54
 */

namespace Mindy\Orm;

use ArrayIterator;

/**
 * Class DataIterator.
 */
class DataIterator extends ArrayIterator
{
    /**
     * @var bool
     */
    public $asArray;
    /**
     * @var QuerySet
     */
    public $qs;

    /**
     * DataIterator constructor.
     *
     * @param array $data
     * @param array $config
     * @param int   $flags
     */
    public function __construct(array $data, array $config = [], $flags = 0)
    {
        parent::__construct($data, $flags);

        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
