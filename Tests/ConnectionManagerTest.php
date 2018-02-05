<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests;

use Doctrine\DBAL\Connection;
use Mindy\Orm\ConnectionManager;
use PHPUnit\Framework\TestCase;

class ConnectionManagerTest extends TestCase
{
    public function testConnection()
    {
        $manager = new ConnectionManager([
            'default' => 'sqlite:///:memory:',
        ]);
        $this->assertInstanceOf(Connection::class, $manager->getConnection());
        $this->assertInstanceOf(Connection::class, $manager->getConnection('default'));
    }
}
