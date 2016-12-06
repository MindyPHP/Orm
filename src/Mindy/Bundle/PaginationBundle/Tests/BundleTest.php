<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 06/12/16
 * Time: 11:25
 */

namespace Mindy\Bundle\PaginationBundle\Tests;

use Mindy\Bundle\PaginationBundle\DependencyInjection\PaginationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class BundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;
    /**
     * @var PaginationExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->extension = new PaginationExtension();
        $this->container = new ContainerBuilder();

        $this->container->set('request_stack', new RequestStack());
        $this->container->set('router', new UrlGenerator(new RouteCollection(), new RequestContext()));

        $this->container->registerExtension($this->extension);
    }

    public function testContainer()
    {
        $this->container->loadFromExtension($this->extension->getAlias());
        $this->container->compile();
        $this->assertTrue($this->container->has('pagination.factory'));
        $this->assertTrue($this->container->has('pagination.data_source.array'));
        $this->assertTrue($this->container->has('pagination.data_source.query_set'));
        $this->assertTrue($this->container->has('pagination.handler'));
    }
}