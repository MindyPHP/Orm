<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 10/10/2016
 * Time: 21:51
 */

namespace Mindy\Bundle\OrmBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $root = $treeBuilder->root('orm');
        $root
            ->children()
                ->arrayNode('connections')->info('Dbal connction parameters')
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifTrue(function ($v) { return !is_array($v); })
                            ->then(function ($v) { return array($v); })
                        ->end()

                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}