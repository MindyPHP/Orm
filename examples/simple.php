<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 03/03/14.03.2014 13:12
 */

if (is_dir(__DIR__ . '/../vendor')) {
    include __DIR__ . '/../vendor/autoload.php';
} else {
    require __DIR__ . '/../src.php';
}

use Mindy\Orm\Model;
use Mindy\Orm\Sync;
use Mindy\Query\Connection;

// Set connection to database
Model::setConnection(new Connection([
    'dsn' => 'mysql:host=localhost;dbname=tmp',
    'username' => 'root',
    'password' => '123456',
    'charset' => 'utf8',
]));

class MyModel extends Model
{

}

$model = new MyModel();

// Create tables in database
$sync = new Sync([
    $model
]);
$sync->create();

assert(true == $sync->hasTable($model));

// TODO https://github.com/studio107/Mindy_Orm/issues/9
// assert(0 === $model->objects()->count());

assert('0' === $model->objects()->count());
assert([] === $model->objects()->all());
