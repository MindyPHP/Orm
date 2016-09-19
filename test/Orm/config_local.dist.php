<?php

return [
    'mysql' => [
        'class' => '\Mindy\Query\Connection',
        'dsn' => 'mysql:host=localhost;dbname=test',
        'username' => 'travis',
        'charset' => 'utf8',
    ],
    'sqlite' => [
        'class' => '\Mindy\Query\Connection',
        'dsn' => 'sqlite::memory:',
    ]
];
