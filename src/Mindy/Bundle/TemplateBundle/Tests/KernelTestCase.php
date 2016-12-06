<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/10/2016
 * Time: 21:51
 */

namespace Mindy\Bundle\TemplateBundle\Tests;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class KernelTestCase extends TestCase
{
    protected function deleteTmpDir($testCase)
    {
        if (!file_exists($dir = sys_get_temp_dir() . '/' . Kernel::VERSION . '/' . $testCase)) {
            return;
        }

        $fs = new Filesystem();
        $fs->remove($dir);
    }

    protected static function getKernelClass()
    {
        require_once __DIR__ . '/AppKernel.php';

        return 'Mindy\Bundle\TemplateBundle\Tests\AppKernel';
    }

    protected static function createKernel(array $options = array())
    {
        $class = self::getKernelClass();

        if (!isset($options['test_case'])) {
            throw new \InvalidArgumentException('The option "test_case" must be set.');
        }

        return new $class(
            $options['test_case'],
            isset($options['root_config']) ? $options['root_config'] : 'config.yml',
            isset($options['environment']) ? $options['environment'] : 'frameworkbundletest' . strtolower($options['test_case']),
            isset($options['debug']) ? $options['debug'] : true
        );
    }
}