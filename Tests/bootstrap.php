<?php

require __DIR__ . '/../src.php';
require __DIR__ . '/TestCase.php';
require __DIR__ . '/DatabaseTestCase.php';

$models = glob(realpath(__DIR__) . '/models/*.php');
foreach($models as $model) {
    include $model;
}

require __DIR__ . '/custom.php';