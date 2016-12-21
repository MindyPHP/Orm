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
