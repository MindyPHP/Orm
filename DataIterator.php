<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
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
