<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/09/16
 * Time: 11:33.
 */
require __DIR__.'/../vendor/autoload.php';

use Mindy\Orm\Tests\Connections;

define('MINDY_ORM_TEST', true);

$mockConnections = new Connections(include(__DIR__.'/connections_settings.php'));
