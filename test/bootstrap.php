<?php

$vendorPath = __DIR__ . '/../vendor';
if (is_dir($vendorPath)) {
    include($vendorPath . '/autoload.php');
} else {
    $vendorPath = __DIR__ . '/../../../../vendor';
    if (is_dir($vendorPath)) {
        include($vendorPath . '/autoload.php');
    } else {
        require __DIR__ . '/../src.php';
    }
}

require __DIR__ . '/DatabaseTestCase.php';

$models = glob(realpath(__DIR__) . '/Modules/Tests/Models/*.php');
foreach ($models as $model) {
    include($model);
}

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