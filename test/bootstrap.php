<?php

use Mindy\Base\Mindy;

define('MINDY_TEST', true);

$vendorPath = include(__DIR__ . '/../vendor/autoload.php');

$models = glob(realpath(__DIR__) . '/Modules/Tests/Models/*.php');
foreach ($models as $model) {
    include($model);
}

$app = Mindy::getInstance([
    'basePath' => __DIR__ . '/app/protected',
    'components' => [
        'db' => [
            'class' => \Mindy\Query\ConnectionManager::class,
            'databases' => require(__DIR__ . '/config_local.php')
        ]
    ]
]);

function d()
{
    $debug = debug_backtrace();
    $args = func_get_args();
    $data = [
        'data' => $args,
        'debug' => [
            'file' => $debug[0]['file'],
            'line' => $debug[0]['line'],
        ]
    ];
    if (class_exists('Mindy\Helper\Dumper')) {
        Mindy\Helper\Dumper::dump($data, 10);
    } else {
        var_dump($data);
    }
    die();
}