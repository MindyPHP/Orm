<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 04/10/16
 * Time: 20:03
 */

namespace Mindy;

use Mindy\Bundle\FrameworkBundle\Console\Application;
use Mindy\Traits\LegacyMethodsTrait;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Mindy
 * @package Mindy
 */
final class App
{
    use LegacyMethodsTrait;

    /**
     * @var bool
     */
    protected $cacheClass;

    /**
     * @var App
     */
    private static $instance;

    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    protected $kernel;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * App constructor.
     * @param string $className
     * @param string $environment
     * @param bool $debug
     */
    final private function __construct(string $className, string $environment, bool $debug)
    {
        $this->debug = $debug;
        $this->kernel = new $className($environment, $debug);
    }

    /**
     * @param string $className
     * @param string $environment
     * @param bool $debug
     * @return App
     */
    public static function createInstance(string $className, string $environment, bool $debug)
    {
        if (self::$instance === null) {
            self::$instance = new self($className, $environment, $debug);
        }
        return self::$instance;
    }

    public function enableDebugHandler()
    {
        Debug::enable();
    }

    /**
     * @param $throw
     * @return App|null
     */
    public static function getInstance($throw = true)
    {
        if (self::$instance === null && $throw) {
            throw new \LogicException(
                'Please run createInstance and create application before get application instance'
            );
        }
        return self::$instance;
    }

    /**
     * Override getter for access to components
     * @param $name
     * @return object
     */
    public function __get($name)
    {
        return $this->getContainer()->get($name);
    }

    /**
     * Clear application instance
     */
    public static function shutdown()
    {
        self::$instance = null;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * @return \Symfony\Component\HttpKernel\Kernel
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    public function enableCache($cacheClass)
    {
        $this->cacheClass = $cacheClass;
    }

    /**
     * Start application
     *
     * @throws \Exception
     */
    public function run()
    {
        if (php_sapi_name() === 'cli') {
            // do run console application

            $input = new ArgvInput();
            $env = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: 'dev');
            $debug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(['--no-debug', '']) && $env !== 'prod';

            if ($debug || $this->debug) {
                Debug::enable();
            }

            $application = new Application($this->getKernel());
            $application->run($input);
        } else {
            // do run web application
            $request = Request::createFromGlobals();

            $kernel = $this->getKernel();

            if (!$this->debug) {
                $kernel->loadClassCache();
            }

            if ($this->cacheClass && class_exists($this->cacheClass)) {
                // add (or uncomment) this new line!
                // wrap the default AppKernel with the AppCache one
                $cacheClass = $this->cacheClass;
                $kernel = new $cacheClass($kernel);
            }

            // actually execute the kernel, which turns the request into a response
            // by dispatching events, calling a controller, and returning the response
            $response = $kernel->handle($request);

            // send the headers and echo the content
            $response->send();

            // triggers the kernel.terminate event
            $kernel->terminate($request, $response);
        }
    }
}