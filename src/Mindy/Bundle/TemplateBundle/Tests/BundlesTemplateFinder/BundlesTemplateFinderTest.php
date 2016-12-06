<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/10/2016
 * Time: 21:51
 */

namespace Mindy\Bundle\TemplateBundle\Tests;

use Mindy\Bundle\TemplateBundle\TemplateFinder\BundlesTemplateFinder;

class BundlesTemplateFinderTest extends KernelTestCase
{
    public function testBundles()
    {
        $kernel = $this->createKernel([
            'debug' => true,
            'environment' => 'dev',
            'test_case' => 'BundlesTemplateFinder'
        ]);
        $kernel->boot();

        $finder = new BundlesTemplateFinder([
            __DIR__ . "/AppBundle",
            __DIR__ . "/Workspace/FooBundle",
            __DIR__ . "/Workspace/ExampleBundle",
        ]);
        $this->assertEquals([
            __DIR__ . "/AppBundle/Resources/templates",
            __DIR__ . "/Workspace/FooBundle/Resources/templates",
        ], $finder->getPaths());

        $this->assertEquals(__DIR__ . "/AppBundle/Resources/templates/main.html", $finder->find('main.html'));

        $this->assertTrue($kernel->getContainer()->has('template.finder.bundles'));
        $this->assertTrue($kernel->getContainer()->has('template.finder.chain'));
        $this->assertTrue($kernel->getContainer()->has('template.finder.templates'));

        $this->assertFalse($kernel->getContainer()->has('template.finder.theme'));

        $this->assertTrue($kernel->getContainer()->has('template'));
        /** @var \Mindy\Template\Renderer $template */
        $template = $kernel->getContainer()->get('template');
        $reflect = new \ReflectionClass($template);
        $property = $reflect->getProperty('options');
        $property->setAccessible(true);
        $options = $property->getValue($template);
        $this->assertArrayHasKey('nl2br', $options['helpers']);
    }
}