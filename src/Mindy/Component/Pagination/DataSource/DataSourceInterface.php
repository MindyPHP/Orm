<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/12/2016
 * Time: 21:26
 */

namespace Mindy\Component\Pagination\DataSource;

interface DataSourceInterface
{
    /**
     * @param $source
     * @return int
     */
    public function getTotal($source);

    /**
     * @param $source
     * @param $page
     * @param $pageSize
     * @return array
     */
    public function applyLimit($source, $page, $pageSize);

    /**
     * @param $source
     * @return bool
     */
    public function supports($source);
}