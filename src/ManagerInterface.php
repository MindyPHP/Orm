<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 19:14
 */

namespace Mindy\Orm;

interface ManagerInterface
{
    /**
     * @param $conditions
     * @return mixed
     */
    public function get($conditions = []);

    /**
     * @param array $conditions
     * @return ManagerInterface
     */
    public function filter($conditions) : ManagerInterface;
}