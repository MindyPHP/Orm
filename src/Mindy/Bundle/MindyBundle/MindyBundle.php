<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/10/2016
 * Time: 18:36
 */

namespace Mindy\Bundle\MindyBundle;

use Doctrine\Common\Inflector\Inflector;
use Mindy\Bundle\MindyBundle\DependencyInjection\Compiler\AdminPass;
use Mindy\Bundle\MindyBundle\DependencyInjection\Compiler\DashboardPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MindyBundle extends Bundle
{
    public function boot()
    {
        Inflector::rules('plural', [
            'uninflected' => ['Menu'],
        ]);
    }

    public function build(ContainerBuilder $container)
    {
        $container->setParameter('admin.menu', []);

        $container->addCompilerPass(new DashboardPass);
        $container->addCompilerPass(new AdminPass);
    }
}