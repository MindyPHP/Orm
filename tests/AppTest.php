<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 04/10/16
 * Time: 20:04
 */

namespace Mindy\Tests;

use Mindy\App;
use Mindy\Kernel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\HttpKernel;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        // $this->removeCache();
        App::shutdown();
    }

    protected function removeCache()
    {
        $fs = new Filesystem();
        $fs->remove(App::getInstance()->getKernel()->getCacheDir());
    }

    protected function createApp()
    {
        return App::getInstance([
            'name' => 'example',
            'debug' => true,
            'environment' => 'dev',
            'rootDir' => __DIR__ . DIRECTORY_SEPARATOR . 'app'
        ]);
    }

    public function testInit()
    {
        $app = $this->createApp();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testCreateContainer()
    {
        $app = $this->createApp();
        $kernel = $app->getKernel();
        $this->assertInstanceOf(Kernel::class, $kernel);
        $this->assertEquals('example', $kernel->getName());
        $this->assertEquals('dev', $kernel->getEnvironment());
        $this->assertTrue(true);
        $this->assertEquals(__DIR__ . '/app', $kernel->getRootDir());
        $this->assertEquals(__DIR__ . '/app/runtime/logs/dev', $kernel->getLogDir());
        $this->assertEquals(__DIR__ . '/app/runtime/cache/dev', $kernel->getCacheDir());
    }

    public function testLegacyMethods()
    {
        $app = $this->createApp();
        $this->assertTrue(method_exists($app, 'getModule'));
        $this->assertTrue(method_exists($app, 'hasModule'));
        $this->assertTrue(method_exists($app, 'getUser'));
        $this->assertTrue(method_exists($app, 'hasComponent'));
        $this->assertTrue(method_exists($app, 'getComponent'));
    }

    protected function getKernel()
    {
        $app = $this->createApp();
        $kernel = $app->getKernel();
        $kernel->boot();
        return $kernel;
    }

    public function testEventDispatcher()
    {
        $kernel = $this->getKernel();
        $this->assertInstanceOf(HttpKernel::class, $kernel->getContainer()->get('http_kernel'));
    }
}