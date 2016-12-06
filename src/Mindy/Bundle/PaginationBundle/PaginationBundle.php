<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 06/12/16
 * Time: 10:59
 */

namespace Mindy\Bundle\PaginationBundle;

use Mindy\Bundle\PaginationBundle\DependencyInjection\Compiler\PaginationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PaginationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PaginationPass());
    }
}