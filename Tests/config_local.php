<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'mysql' => [
        'dbname' => 'test',
        'user' => 'root',
        'password' => '',
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
    ],
    'sqlite' => [
        'memory' => true,
//        'path' => __DIR__ . '/sqlite.db',
        'driver' => 'pdo_sqlite',
        'driverClass' => 'Mindy\QueryBuilder\Driver\SqliteDriver',
    ],
    'pgsql' => [
        'dbname' => 'test',
        'user' => 'postgres',
        'password' => '',
        'host' => 'localhost',
        'driver' => 'pdo_pgsql',
    ],
];
