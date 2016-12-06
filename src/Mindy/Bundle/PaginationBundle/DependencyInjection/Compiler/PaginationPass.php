<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 06/12/16
 * Time: 11:22
 */

namespace Mindy\Bundle\PaginationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PaginationPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pagination.factory')) {
            return;
        }

        $definition = $container->getDefinition('pagination.factory');
        foreach ($container->findTaggedServiceIds('pagination.data_source') as $id => $params) {
            $definition->addMethodCall('addDataSource', array(new Reference($id)));
        }
    }
}