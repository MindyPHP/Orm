<?php

namespace Mindy\Component\Pagination;

use Mindy\Component\Pagination\DataSource\DataSourceInterface;
use Mindy\Component\Pagination\Handler\PaginationHandlerInterface;

/**
 * Class BasePagination
 * @package Mindy\Pagination
 */
abstract class BasePagination
{
    /**
     * @var int current pagination id
     */
    protected $id;

    /**
     * @var int
     */
    protected $total = 0;

    /**
     * @var int current page
     */
    protected $page;

    /**
     * @var string
     */
    protected $pageKey;

    /**
     * @var int default page size
     */
    protected $pageSize;

    /**
     * @var string
     */
    protected $pageSizeKey;

    /**
     * @var PaginationHandlerInterface
     */
    protected $handler;

    /**
     * @var DataSourceInterface
     */
    protected $dataSource;

    /**
     * @var mixed
     */
    protected $source;

    /**
     * @var int autoincrement pagination classes on the page
     */
    private static $_id = 0;
    /**
     * @var array
     */
    protected $pageSizes = [10, 25, 50, 100];

    /**
     * BasePagination constructor.
     * @param $source
     * @param array $config
     * @param PaginationHandlerInterface $handler
     * @param DataSourceInterface $dataSource
     */
    public function __construct($source, array $config = [], PaginationHandlerInterface $handler, DataSourceInterface $dataSource)
    {
        self::$_id++;
        $this->id = self::$_id;

        $this->source = $source;
        $this->dataSource = $dataSource;

        foreach (['page', 'pageSize', 'pageSizes'] as $key) {
            if (array_key_exists($key, $config)) {
                $this->{$key} = $config[$key];
            }
        }

        $this->handler = $handler;

        if (null === $this->page) {
            $this->page = $handler->getPage($this->getPageKey(), 1);
        }

        if (null === $this->pageSize) {
            $this->pageSize = $handler->getPageSize($this->getPageSizeKey(), 10);
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return PageSize
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @param $pageSize
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = (int)$pageSize;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageSizeKey()
    {
        return empty($this->pageSizeKey) ? $this->getPageKey() . '_PageSize' : $this->pageSizeKey;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return integer number of pages
     */
    public function getPagesCount()
    {
        $total = $this->getTotal();
        if ($total > 0) {
            return (int)ceil($total / $this->getPageSize());
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        $page = $this->page;
        if ($page > $this->getPagesCount() && $this->getTotal()) {
            return $this->getPagesCount();
        }
        return (int)$page;
    }

    /**
     * @param $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = (int)$page;
        return $this;
    }

    /**
     * Apply limits to source
     * @throws \Exception
     * @return $this
     */
    public function paginate()
    {
        $this->total = $this->dataSource->getTotal($this->source);
        if (
            $this->getPageSize() > $this->total ||
            ceil($this->total / $this->getPageSize()) < $this->getPage()
        ) {
            $this->handler->wrongPageCallback();
        }

        return $this->dataSource->applyLimit(
            $this->source,
            $this->getPage(),
            $this->getPageSize()
        );
    }

    /**
     * @return string
     */
    public function getPageKey()
    {
        if ($this->pageKey === null) {
            return sprintf('Pager_%s', $this->id);
        } else {
            return $this->pageKey;
        }
    }

    /**
     * @return array
     */
    public function getPageSizes()
    {
        return $this->pageSizes;
    }

    /**
     * @return PaginationView
     */
    public function createView()
    {
        return new PaginationView([
            'total' => $this->getTotal(),
            'page' => $this->getPage(),
            'page_sizes' => $this->getPageSizes(),
            'page_size' => $this->getPageSize(),
            'page_count' => $this->getPagesCount(),
            'page_key' => $this->getPageKey(),
            'page_size_key' => $this->getPageSizeKey()
        ], $this->handler);
    }

    /**
     * Reset id counter
     */
    public static function resetCounter()
    {
        self::$_id = 0;
    }
}
