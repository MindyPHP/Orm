<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/12/2016
 * Time: 21:26
 */

namespace Mindy\Component\Pagination\DataSource;

/**
 * Class ArrayDataSource
 * @package Mindy\Component\Pagination\DataSource
 */
class ArrayDataSource implements DataSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTotal($source)
    {
        return count($source);
    }

    /**
     * {@inheritdoc}
     */
    public function applyLimit($source, $page, $pageSize)
    {
        return array_slice($source, $pageSize * ($page <= 1 ? 0 : $page - 1), $pageSize);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($source)
    {
        return is_array($source);
    }
}