<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/10/2016
 * Time: 20:50
 */

namespace Mindy\Bundle\TemplateBundle\DependencyInjection;

use Mindy\Template\Expression;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class TemplateExtension extends Extension
{

    /**
     * Loads a specific configuration.
     *
     * @param array $configs An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('template.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('template.mode', $config['mode']);
        $container->setParameter('template.helpers', $config['helpers']);

        if ($this->isConfigEnabled($container, $config['theme'])) {
            $this->registerThemeTemplateFinderConfiguration($config['theme'], $container, $loader);
        }

        if ($this->isConfigEnabled($container, $config['bundles'])) {
            $this->registerBundlesTemplateFinderConfiguration($config['bundles'], $container, $loader);
        }
    }

    protected function registerBundlesTemplateFinderConfiguration(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $loader->load('bundles_finder.xml');

        $bundlesDefinition = $container->findDefinition('template.finder.bundles');

        $dirs = [];
        $templatesDir = $bundlesDefinition->getArgument('templates_dir');
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFileName()))) {
                if (is_dir(sprintf('%s/Resources/%s', $dir, $templatesDir))) {
                    $dirs[] = $dir;
                }
            }
        }
        $bundlesDefinition->replaceArgument('bundles_dirs', $dirs);

        $definition = $container->findDefinition('template.finder.chain');
        $definition->addMethodCall('addFinder', array(new Reference('template.finder.bundles')));

        $this->addClassesToCompile(array(
            'Mindy\\Bundle\\TemplateBundle\\TemplateFinder\\BundlesTemplateFinder',
            $container->findDefinition('template.finder.bundles')->getClass(),
        ));
    }

    protected function registerThemeTemplateFinderConfiguration(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        $loader->load('theme_finder.xml');

        $definition = $container->findDefinition('template.finder.chain');
        $definition->addMethodCall('addFinder', array(new Reference('template.finder.theme')));

        $container->setParameter('template.theme', $config['theme']);

        $this->addClassesToCompile(array(
            'Mindy\\Bundle\\TemplateBundle\\TemplateFinder\\ThemeTemplateFinder',
            $container->findDefinition('template.finder.theme')->getClass(),
        ));
    }
}