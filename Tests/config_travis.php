<?php

return [
    'default' => [
        'class' => '\Mindy\Query\Connection',
        'dsn' => 'mysql:host=127.0.0.1;dbname=test',
        'username' => 'root',
        'charset' => 'utf8',
    ],
    'sqlite' => [
        'class' => '\Mindy\Query\Connection',
        'dsn' => 'sqlite::memory:',
    ]
];
