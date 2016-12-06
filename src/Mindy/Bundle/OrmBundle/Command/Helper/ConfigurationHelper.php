<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 10/10/2016
 * Time: 20:07
 */

namespace Mindy\Bundle\OrmBundle\Command\Helper;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Tools\Console\Helper\ConfigurationHelper as BaseConfigurationHelper;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigurationHelper extends BaseConfigurationHelper implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * ModuleConfigurationHelper constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * @return \Symfony\Component\HttpKernel\Kernel
     */
    protected function getKernel()
    {
        return $this->container->get('kernel');
    }

    /**
     * @param InputInterface $input
     * @param OutputWriter $outputWriter
     * @return Configuration
     * @throws \Exception
     */
    public function getMigrationConfig(InputInterface $input, OutputWriter $outputWriter)
    {
        $connection = $this->container->get('db')->getConnection($input->getOption('connection'));

        $configuration = new Configuration($connection);
        $bundleName = $input->getOption('bundle');
        if (!$bundleName) {
            throw new Exception('Missing bundle name');
        }
        $bundle = $this->getKernel()->getBundle($bundleName);

        $configuration->setName($bundle->getName());
        $configuration->setMigrationsTableName($this->normalizeName($bundleName));
        $configuration->setMigrationsNamespace(sprintf(
            'Mindy\Bundle\%s\Migrations', $bundle->getName()
        ));

        $dir = sprintf("%s/Migrations", $bundle->getPath());
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $configuration->setMigrationsDirectory($dir);

        return $configuration;
    }

    /**
     * @param string $name
     * @return string
     */
    public function normalizeName(string $name) : string
    {
        $cleanName = str_replace('Bundle', '', $name);
        $normalizedName = trim(strtolower(preg_replace('/(?<![A-Z])[A-Z]/', '_\0', $cleanName)), '_');
        return sprintf("%s_migrations", $normalizedName);
    }
}