<?php

return [
    'default' => [
        'class' => '\Mindy\Query\Connection',
        'dsn' => 'mysql:host=localhost;dbname=tmp',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
    ],
    'sqlite' => [
        'class' => '\Mindy\Query\Connection',
        'dsn' => 'sqlite::memory:',
    ]
];
