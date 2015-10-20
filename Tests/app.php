<?php

defined('MINDY_PATH') or define('MINDY_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

$debug = true;
if ($debug) {
    defined('MINDY_DEBUG') or define('MINDY_DEBUG', true);
    defined('MINDY_TRACE_LEVEL') or define('MINDY_TRACE_LEVEL', 3);
    defined('MINDY_ENABLE_ERROR_HANDLER') or define('MINDY_ENABLE_ERROR_HANDLER', true);
    defined('MINDY_ENABLE_EXCEPTION_HANDLER') or define('MINDY_ENABLE_EXCEPTION_HANDLER', true);
    ini_set('error_reporting', -1);
}

$app = \Mindy\Base\Mindy::getInstance([
    'basePath' => dirname(__FILE__),
    'name' => 'Mindy',
    'locale' => [
        'language' => 'en',
        'sourceLanguage' => 'en',
        'charset' => 'utf-8',
    ],
    'components' => [
        'db' => [
            'class' => '\Mindy\Query\ConnectionManager',
            'databases' => getenv('TRAVIS') ? require_once('config_travis.php') : require_once('config_local.php')
        ],
        'cache' => [
            'class' => '\Mindy\Cache\DummyCache'
        ],
        'logger' => [
            'class' => '\Mindy\Logger\LoggerManager',
            'handlers' => [
                'null' => [
                    'class' => '\Mindy\Logger\Handler\NullHandler',
                    'level' => 'ERROR'
                ],
            ],
        ],
        'storage' => [
            'class' => '\Mindy\Storage\FileSystemStorage'
        ],
    ],
    'preload' => ['log', 'db'],
    'modules' => []
]);