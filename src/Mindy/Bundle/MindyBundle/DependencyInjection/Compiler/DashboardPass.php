<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 14/11/2016
 * Time: 20:40
 */

namespace Mindy\Bundle\MindyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DashboardPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('admin.dashboard')) {
            return;
        }

        $definition = $container->getDefinition('admin.dashboard');
        if ($definition) {
            foreach ($container->findTaggedServiceIds('admin.dashboard.widget') as $id => $attributes) {
                $definition->addMethodCall('addWidget', array(
                    new Reference($id)
                ));
            }
        }
    }
}