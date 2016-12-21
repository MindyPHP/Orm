<?php

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
        'user' => 'root',
        'password' => '',
        'host' => 'localhost',
        'driver' => 'pdo_pgsql',
    ],
];
