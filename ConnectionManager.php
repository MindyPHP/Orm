<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

/**
 * Class ConnectionManager
 */
class ConnectionManager
{
    /**
     * @var Connection[]
     */
    protected $connections = [];
    /**
     * @var null
     */
    protected $configuration = null;
    /**
     * @var null
     */
    protected $eventManager = null;

    /**
     * ConnectionManager constructor.
     *
     * @param array              $connections
     * @param Configuration|null $configuration
     * @param EventManager|null  $eventManager
     */
    public function __construct(array $connections = [], Configuration $configuration = null, EventManager $eventManager = null)
    {
        $this->configuration = $configuration ?? new Configuration();
        $this->eventManager = $eventManager ?? new EventManager();

        foreach ($connections as $name => $config) {
            $this->connections[$name] = $this->createConnection($config);
        }
    }

    /**
     * @param string|array $config
     *
     * @return Connection
     */
    protected function createConnection($config): Connection
    {
        if (is_string($config)) {
            $config = ['url' => $config];
        }

        return DriverManager::getConnection($config, $this->configuration, $this->eventManager);
    }

    /**
     * @param string $name
     *
     * @return Connection|null
     */
    public function getConnection(string $name = 'default')
    {
        return $this->connections[$name];
    }
}
