<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
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
final class ConnectionManager
{
    /**
     * @var Connection[]
     */
    private $connections = [];
    /**
     * @var string
     */
    private $defaultConnection = 'default';
    /**
     * @var null
     */
    private $configuration = null;
    /**
     * @var null
     */
    private $eventManager = null;

    /**
     * ConnectionManager constructor.
     *
     * @param array              $connections
     * @param string             $defaultConnection
     * @param Configuration|null $configuration
     * @param EventManager|null  $eventManager
     */
    public function __construct(array $connections = [], string $defaultConnection = 'default', Configuration $configuration = null, EventManager $eventManager = null)
    {
        $this->defaultConnection = $defaultConnection;
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
    private function createConnection($config): Connection
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
    public function getConnection(string $name = null)
    {
        if (empty($name)) {
            $name = $this->defaultConnection;
        }

        if (false === array_key_exists($name, $this->connections)) {
            return null;
        }

        return $this->connections[$name];
    }
}
