<?php

return [
    'mysql' => [
        'url' => 'mysql://root@127.0.0.1/test?charset=utf8',
        'driver' => 'pdo_mysql'
    ],
    'pgsql' => [
        'url' => 'pgsql://root@localhost:5432/test',
        'driver' => 'pdo_pgsql'
    ],
    'sqlite' => [
        'path' => __DIR__ . '/sqlite.db',
        'user' => '',
        'password' => '',
        'host' => 'localhost',
        'memory' => false,
        'driverClass' => '\Mindy\QueryBuilder\Driver\SqliteDriver',
    ]
];
