<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Bundle\TemplateBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds tagged routing.loader services to routing.resolver service.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TemplatePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('template')) {
            return;
        }

        $definitionChain = $container->getDefinition('template.finder.chain');
        if ($definitionChain) {
            foreach ($container->findTaggedServiceIds('template.finder') as $id => $attributes) {
                $definitionChain->addMethodCall('addFinder', array(new Reference($id)));
            }
        }

        $definition = $container->getDefinition('template');
        if ($definition) {
            foreach ($container->findTaggedServiceIds('template.library') as $id => $attributes) {
                $definition->addMethodCall('addLibrary', array(new Reference($id)));
            }

            foreach ($container->findTaggedServiceIds('template.helper') as $id => $attributes) {
                $definition->addMethodCall('addHelper', array(key($attributes), current($attributes)));
            }

            foreach ($container->findTaggedServiceIds('template.variable_provider') as $id => $attributes) {
                $definition->addMethodCall('addVariableProvider', array(new Reference($id)));
            }
        }

        if ($container->hasParameter('template.helpers')) {
            foreach ($container->getParameter('template.helpers') as $id => $func) {
                $definition->addMethodCall('addHelper', array($id, $func));
            }
        }
    }
}
