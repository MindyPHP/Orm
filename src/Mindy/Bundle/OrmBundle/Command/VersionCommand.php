<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 10/10/2016
 * Time: 20:27
 */

namespace Mindy\Bundle\OrmBundle\Command;

use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand as BaseVersionCommand;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class VersionCommand extends BaseVersionCommand implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use MigrationConfigurationTrait;
}