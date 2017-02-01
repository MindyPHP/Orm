<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

require __DIR__.'/../vendor/autoload.php';

use Mindy\Orm\Tests\Connections;

define('MINDY_ORM_TEST', true);

$mockConnections = new Connections(include(__DIR__.'/connections_settings.php'));
