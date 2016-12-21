<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 07/12/2016
 * Time: 16:04.
 */

namespace Mindy\Orm;

use Doctrine\Dbal\Connection;

/**
 * Interface QuerySetInterface.
 */
interface QuerySetInterface
{
    /**
     * @param $conditions
     *
     * @return array|ModelInterface
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
     * @param string|Connection $connection
     *
     * @return $this
     */
    public function setConnection($connection);

    /**
     * @return Connection
     */
    public function getConnection();

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
