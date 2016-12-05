<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 04/10/16
 * Time: 20:04
 */

namespace Mindy\Component\Application\Tests;

use Mindy\Component\Application\App;
use Symfony\Component\Filesystem\Filesystem;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        parent::tearDown();

        App::shutdown();

        $fs = new Filesystem();
        $fs->remove(__DIR__ . '/runtime');
    }

    public function testInit()
    {
        $app = App::createInstance(AppKernel::class, 'dev', true);
        $this->assertInstanceOf(App::class, $app);

        $this->assertTrue(method_exists($app, 'getUser'));
        $this->assertTrue(method_exists($app, 'hasComponent'));
        $this->assertTrue(method_exists($app, 'getComponent'));
    }
}