<?php

set_error_handler(function() {
    var_dump(func_get_args());
}, E_WARNING);

use Mindy\Base\Mindy;

define('MINDY_TEST', true);

$vendorPath = include(__DIR__ . '/../vendor/autoload.php');

$models = glob(realpath(__DIR__) . '/Modules/Tests/Models/*.php');
foreach ($models as $model) {
    include($model);
}

use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory as CacheStore;
use Mindy\Helper\Alias;

$app = Mindy::getInstance([
    'basePath' => __DIR__ . '/app/protected',
    'webPath' => __DIR__ . '/app',
    'components' => [
        'db' => [
            'class' => \Mindy\Query\ConnectionManager::class,
            'databases' => require(__DIR__ . (@getenv('TRAVIS') ? '/config_travis.php' : '/config_local.php'))
        ],
        'storage' => [
            'class' => '\Mindy\Storage\Storage',
            'adapters' => [
                'default' => function () {
                    $path = Alias::get('www.media');
                    // Create the adapter
                    $localAdapter = new Local($path);
                    // Create the cache store
                    $cacheStore = new CacheStore();
                    // Decorate the adapter
                    return new CachedAdapter($localAdapter, $cacheStore);
                }
            ]
        ],
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