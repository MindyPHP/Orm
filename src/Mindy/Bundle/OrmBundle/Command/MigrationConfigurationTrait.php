<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 10/10/2016
 * Time: 20:30
 */

namespace Mindy\Bundle\OrmBundle\Command;

use Mindy\Bundle\OrmBundle\Command\Helper\ConfigurationHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputOption;

trait MigrationConfigurationTrait
{
    protected function configure()
    {
        parent::configure();

        $this->addOption('bundle', 'b', InputOption::VALUE_REQUIRED);
        $this->addOption('connection', 'c', InputOption::VALUE_OPTIONAL, 'Connection name', 'default');
    }

    public function setHelperSet(HelperSet $helperSet)
    {
        $helperSet->set(new ConfigurationHelper($this->container), 'configuration');
        parent::setHelperSet($helperSet);
    }
}