<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/12/2016
 * Time: 22:33
 */

namespace Mindy\Component\Pagination\DataSource;

use Mindy\Orm\Manager;
use Mindy\Orm\QuerySet;

class QuerySetDataSource implements DataSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTotal($source)
    {
        if ($source instanceof Manager) {
            $source = $source->getQuerySet();
        }
        $clone = clone $source;
        return $clone->count();
    }

    /**
     * {@inheritdoc}
     */
    public function applyLimit($source, $page, $pageSize)
    {
        if ($source instanceof Manager) {
            $source = $source->getQuerySet();
        }
        $clone = clone $source;
        return $clone->paginate($page, $pageSize)->all();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($source)
    {
        return $source instanceof QuerySet || $source instanceof Manager;
    }
}