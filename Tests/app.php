<?php

defined('MINDY_PATH') or define('MINDY_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

$debug = true;
if ($debug) {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);
    defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', true);
    defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER', true);
    ini_set('error_reporting', -1);
}

$app = \Mindy\Base\Mindy::getInstance([
    'basePath' => dirname(__FILE__) . '/protected',
    'name' => 'Mindy',
    'locale' => [
        'language' => 'en',
        'sourceLanguage' => 'en',
        'charset' => 'utf-8',
    ],
    'components' => [
        'db' => [
            'class' => '\Mindy\Query\ConnectionManager',
            'databases' => require_once('config_local.php')
        ],
        'cache' => [
            'class' => '\Mindy\Cache\DummyCache'
        ],
    ],
    'preload' => ['log', 'db'],
    'modules' => []
]);