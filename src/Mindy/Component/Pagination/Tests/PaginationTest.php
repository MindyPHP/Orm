<?php

namespace Mindy\Component\Pagination\Tests;

use Mindy\Component\Pagination\DataSource\ArrayDataSource;
use Mindy\Component\Pagination\Handler\RequestPaginationHandler;
use Mindy\Component\Pagination\Pagination;
use Mindy\Component\Pagination\PaginationFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 17/04/14.04.2014 16:45
 */
class PaginationTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Pagination::resetCounter();
    }

    protected function getHandler(Request $request = null)
    {
        $routes = new RouteCollection();
        $routes->add('list', new Route('/'));

        $requestStack = new RequestStack();
        $requestStack->push($request ? $request : Request::create('/'));
        $handler = new RequestPaginationHandler(
            $requestStack,
            new UrlGenerator($routes, new RequestContext('/'))
        );
        $handler->setIncorrectPageCallback(function ($h) {
            throw new \RuntimeException('Incorrect page');
        });
        return $handler;
    }

    public function createPagination($source, array $options = [], $handler = null)
    {
        $factory = new PaginationFactory();
        $factory->addDataSource(new ArrayDataSource());
        return $factory->createPagination($source, $options, $handler ? $handler : $this->getHandler());
    }

    public function testId()
    {
        $pager = $this->createPagination([]);
        $this->assertSame(1, $pager->getId());
        $this->assertSame('Pager_1', $pager->getPageKey());
        $this->assertSame('Pager_1_PageSize', $pager->getPageSizeKey());

        $pager = $this->createPagination([]);
        $this->assertSame(2, $pager->getId());
        $this->assertSame('Pager_2', $pager->getPageKey());
        $this->assertSame('Pager_2_PageSize', $pager->getPageSizeKey());
    }

    public function testEmpty()
    {
        $pager = $this->createPagination([]);
        $this->assertInstanceOf(Pagination::class, $pager);

        $this->assertSame(0, $pager->getTotal());
        $this->assertSame(1, $pager->getPage());
        $this->assertSame(10, $pager->getPageSize());
        $this->assertSame(0, $pager->getPagesCount());

        $pager->setPage(2);
        $this->assertSame(2, $pager->getPage());
        $pager->setPageSize(20);
        $this->assertSame(20, $pager->getPageSize());
    }

    public function testPaginateApi()
    {
        $source = range(1, 20);
        $this->assertSame(20, count($source));

        $pager = $this->createPagination($source);
        $this->assertInstanceOf(Pagination::class, $pager);

        $pager->paginate();
        $this->assertSame(20, $pager->getTotal());
        $this->assertSame(1, $pager->getPage());
        $this->assertSame(10, $pager->getPageSize());
        $this->assertSame(2, $pager->getPagesCount());

        $pager->setPage(2)->setPageSize(2)->paginate();
        $this->assertSame(20, $pager->getTotal());
        $this->assertSame(2, $pager->getPage());
        $this->assertSame(2, $pager->getPageSize());
        $this->assertSame(10, $pager->getPagesCount());
    }

    public function testPaginate()
    {
        $pager = $this->createPagination([], [],
            $this->getHandler(Request::create('/?Pager_1=2&Pager_1_PageSize=30'))
        );
        $this->assertSame('Pager_1', $pager->getPageKey());
        $this->assertSame(2, $pager->getPage());
        $this->assertSame('Pager_1_PageSize', $pager->getPageSizeKey());
        $this->assertSame(30, $pager->getPageSize());

        $pager = $this->createPagination([], [],
            $this->getHandler(Request::create('/?Pager_2=2&Pager_1_PageSize=30'))
        );
        $this->assertSame('Pager_2', $pager->getPageKey());
        $this->assertSame(2, $pager->getPage());
        $this->assertSame('Pager_2_PageSize', $pager->getPageSizeKey());
        $this->assertSame(10, $pager->getPageSize());
    }

    public function testView()
    {
        $source = range(1, 20);
        $this->assertSame(20, count($source));

        $pager = $this->createPagination($source);
        $this->assertInstanceOf(Pagination::class, $pager);

        $data = $pager->setPage(2)->setPageSize(2)->paginate();
        $this->assertSame(2, count($data));

        $view = $pager->createView();
        $this->assertSame(20, $view->getTotal());
        $this->assertSame(2, $view->getPage());
        $this->assertSame(2, $view->getPageSize());
        $this->assertSame(10, $view->getPagesCount());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Incorrect page
     */
    public function testWrongPage()
    {
        $pager = $this->createPagination(range(1, 20), [
            'page' => 2,
            'pageSize' => 30
        ]);
        $this->assertSame(2, $pager->getPage());
        $this->assertSame(30, $pager->getPageSize());

        $data = $pager->paginate();
        $this->assertSame([], $data);
    }
}
