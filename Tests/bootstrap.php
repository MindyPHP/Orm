<?php

if(is_dir(__DIR__ . '/../vendor')) {
    include(__DIR__ . '/../vendor/autoload.php');
} else {
    require __DIR__ . '/../src.php';
}

require __DIR__ . '/TestCase.php';
require __DIR__ . '/DatabaseTestCase.php';

$models = glob(realpath(__DIR__) . '/models/*.php');
foreach($models as $model) {
    include $model;
}

require __DIR__ . '/custom.php';
@unlink(__DIR__ . '/../runtime/logs/app.log');
