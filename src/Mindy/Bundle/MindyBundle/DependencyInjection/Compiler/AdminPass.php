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

class AdminPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('admin.registry')) {
            return;
        }

        $definition = $container->getDefinition('admin.registry');
        if ($definition) {
            foreach ($container->findTaggedServiceIds('admin.admin') as $id => $params) {
                $attributes = array_shift($params);
                if (isset($attributes['slug'])) {
                    $definition->addMethodCall('addAdmin', [
                        $id,
                        $attributes['slug']
                    ]);
                }
            }
        }
    }
}