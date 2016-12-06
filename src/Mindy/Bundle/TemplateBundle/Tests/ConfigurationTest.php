<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Bundle\TemplateBundle\Tests\DependencyInjection;

use Mindy\Bundle\TemplateBundle\DependencyInjection\Configuration;
use Mindy\Component\Template\Renderer;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), []);

        $this->assertEquals($config, self::getBundleDefaultConfig());
    }

    protected static function getBundleDefaultConfig()
    {
        return [
            'mode' => Renderer::RECOMPILE_NORMAL,
            'helpers' => [],
            'bundles' => [
                'enabled' => true,
                'templatesDir' => 'templates'
            ],
            'theme' => [
                'enabled' => false,
                'basePath' => '%kernel.root_dir%/Resources',
                'theme' => 'default',
                'templatesDir' => 'templates'
            ],
            'templates' => [
                'enabled' => true,
                'basePath' => '%kernel.root_dir%/Resources',
                'templatesDir' => 'templates'
            ]
        ];
    }
}
