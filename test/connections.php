<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/09/16
 * Time: 11:32
 */

namespace Mindy\Tests;

use Mindy\QueryBuilder\ConnectionManager;

$path = (@getenv('TRAVIS') ? '/config_travis.php' : '/config_local.php');
$databases = require(__DIR__ . $path);
return new ConnectionManager($databases);