<?php

return [
    'mysql' => [
        'class' => '\Mindy\Query\Connection',
        'dsn' => 'mysql:host=localhost;dbname=test',
        'username' => 'root',
        'charset' => 'utf8',
    ],
    'sqlite' => [
        'class' => '\Mindy\Query\Connection',
        'dsn' => 'sqlite::memory:',
    ],
    'pgsql' => [
        'class' => '\Mindy\Query\Connection',
        'dsn' => 'pgsql:host=localhost;dbname=test',
        'username' => 'postgres'
    ]
];
