<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/12/2016
 * Time: 22:38
 */

namespace Mindy\Component\Pagination;

use Mindy\Component\Pagination\Handler\PaginationHandlerInterface;

/**
 * Class PaginationView
 * @package Mindy\Component\Pagination
 */
class PaginationView
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var PaginationHandlerInterface
     */
    protected $handler;

    /**
     * PaginationView constructor.
     * @param array $data
     * @param PaginationHandlerInterface $handler
     */
    public function __construct(array $data, PaginationHandlerInterface $handler)
    {
        $this->data = $data;
        $this->handler = $handler;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->data['total'];
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->data['page'];
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->data['page_size'];
    }

    /**
     * @return int
     */
    public function getPagesCount()
    {
        return $this->data['page_count'];
    }

    /**
     * @return int[]
     */
    public function getPageSizes()
    {
        return $this->data['page_sizes'];
    }

    /**
     * @param int|string $page
     * @return string
     */
    public function getUrl($page)
    {
        return $this->handler->getUrlForQueryParam($this->data['page_key'], $page);
    }

    /**
     * @param int|string $pageSize
     * @return string
     */
    public function urlPageSize($pageSize)
    {
        return $this->handler->getUrlForQueryParam($this->data['page_size_key']->getPageSizeKey(), $pageSize);
    }

    /**
     * @param $page
     * @return bool
     */
    public function hasPage($page)
    {
        return $page > 0 && $page <= $this->getPagesCount();
    }

    /**
     * @return bool
     */
    public function hasNextPage()
    {
        return $this->hasPage($this->getPage() + 1);
    }

    /**
     * @return bool
     */
    public function hasPrevPage()
    {
        return $this->hasPage($this->getPage() - 1);
    }

    /**
     * @param int $count
     * @return array
     */
    public function iterPrevPage($count = 3)
    {
        if ($this->getPage() == $this->getPagesCount() && $this->getPagesCount() - $count * 2 > 0) {
            $count *= 2;
        }
        $pages = [];
        foreach (array_reverse(range(1, $count)) as $i) {
            $page = $this->getPage() - $i;
            if ($page > 0) {
                $pages[] = $page;
            }
        }
        return $pages;
    }

    /**
     * @param int $count
     * @return array
     */
    public function iterNextPage($count = 3)
    {
        if ($this->getPage() == 1 && $this->getPagesCount() >= $count * 2) {
            $count *= 2;
        }
        $pages = [];
        foreach (range(1, $count) as $i) {
            $page = $this->getPage() + $i;
            if ($page <= $this->getPagesCount()) {
                $pages[] = $page;
            }
        }
        return $pages;
    }
}