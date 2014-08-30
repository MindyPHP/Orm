<?php

return [
    'mysql' => [
        'class' => '\Mindy\Query\Connection',
        'dsn' => 'mysql:host=localhost;dbname=tmp',
        'username' => 'root',
        'password' => '123456',
        'charset' => 'utf8',
    ],
    'default' => [
        'class' => '\Mindy\Query\Connection',
        'dsn' => 'sqlite::memory:',
    ]
];
