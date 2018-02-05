<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

use ArrayAccess;
use IteratorAggregate;
use Mindy\Orm\Exception\MultipleObjectsReturned;

/**
 * Interface QuerySetInterface.
 */
interface QuerySetInterface extends ConnectionAwareInterface, IteratorAggregate, ArrayAccess
{
    /**
     * Executes query and returns a single row of result.
     *
     * @param $conditions
     *
     * @throws MultipleObjectsReturned
     *
     * @return array|ModelInterface|null
     */
    public function get($conditions = []);

    /**
     * @param array $conditions
     *
     * @return $this
     */
    public function filter($conditions);

    /**
     * @param array $conditions
     *
     * @return $this
     */
    public function orFilter($conditions);

    /**
     * @param array $conditions
     *
     * @return $this
     */
    public function exclude($conditions);

    /**
     * @param array $conditions
     *
     * @return $this
     */
    public function orExclude($conditions);

    /**
     * @return array|ModelInterface[]
     */
    public function all();

    /**
     * Update records.
     *
     * @param array $attributes
     *
     * @return int updated records
     */
    public function update(array $attributes);

    /**
     * @param string $q
     *
     * @return int
     */
    public function count($q = '*');

    /**
     * @param array|string $columns
     *
     * @return $this
     */
    public function order($columns);

    /**
     * @param int $batchSize
     *
     * @return \Mindy\Orm\BatchDataIterator
     */
    public function batch($batchSize = 100);

    /**
     * @param int $batchSize
     *
     * @return \Mindy\Orm\BatchDataIterator
     */
    public function each($batchSize = 100);

    /**
     * @param $page
     * @param int $pageSize
     *
     * @return $this
     */
    public function paginate($page, $pageSize = 10);

    /**
     * @param $limit
     *
     * @return static
     */
    public function limit($limit);

    /**
     * @param $offset int
     *
     * @return static
     */
    public function offset($offset);

    /**
     * @param string|array $fields
     *
     * @return $this
     */
    public function group($fields);

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function asArray($value = true);

    /**
     * @param null|string|array $q
     *
     * @return float|int
     */
    public function min($q);

    /**
     * @param null|string|array $q
     *
     * @return float|int
     */
    public function max($q);

    /**
     * @param null|string|array $q
     *
     * @return float|int
     */
    public function sum($q);

    /**
     * @param null|string|array $q
     *
     * @return float|int
     */
    public function average($q);

    /**
     * @param string|bool $fields
     *
     * @return $this
     */
    public function distinct($fields = true);
}
