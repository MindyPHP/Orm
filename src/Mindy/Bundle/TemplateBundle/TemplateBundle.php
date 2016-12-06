<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 05/10/2016
 * Time: 20:50
 */

namespace Mindy\Bundle\TemplateBundle;

use Mindy\Bundle\TemplateBundle\DependencyInjection\Compiler\TemplatePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TemplateBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TemplatePass());
    }
}