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

require __DIR__ . '/app.php';
require __DIR__ . '/DatabaseTestCase.php';

require __DIR__ . '/Cases/Orm/AggregationTest.php';
require __DIR__ . '/Cases/Orm/AutoSlugFieldTest.php';
require __DIR__ . '/Cases/Orm/BasicTest.php';
require __DIR__ . '/Cases/Orm/HasManyFieldTest.php';
require __DIR__ . '/Cases/Orm/LookupRelationTest.php';
require __DIR__ . '/Cases/Orm/LookupTest.php';
require __DIR__ . '/Cases/Orm/ManagerTest.php';
require __DIR__ . '/Cases/Orm/ManyToManyFieldTest.php';
require __DIR__ . '/Cases/Orm/OrderByLookupTest.php';
require __DIR__ . '/Cases/Orm/QueryTest.php';
require __DIR__ . '/Cases/Orm/SaveUpdateTest.php';
require __DIR__ . '/Cases/Orm/SubqueriesTest.php';
require __DIR__ . '/Cases/Orm/SyncTest.php';
require __DIR__ . '/Cases/Orm/TreeModelTest.php';

$models = glob(realpath(__DIR__) . '/Modules/Tests/Models/*.php');
foreach ($models as $model) {
    include($model);
}

function d()
{
    $debug = debug_backtrace();
    $args = func_get_args();
    $data = array(
        'data' => $args,
        'debug' => array(
            'file' => $debug[0]['file'],
            'line' => $debug[0]['line'],
        )
    );
    if (class_exists('Mindy\Helper\Dumper')) {
        Mindy\Helper\Dumper::dump($data, 10);
    } else {
        var_dump($data);
    }
    die();
}