<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/09/16
 * Time: 11:33
 */

$autoloader = require(__DIR__ . '/../vendor/autoload.php');

define('MINDY_ORM_TEST', true);

class Connections
{
    protected static $cm;

    public function __construct(\Mindy\QueryBuilder\ConnectionManager $cm)
    {
        self::$cm = $cm;
    }

    public static function getConnectionManager()
    {
        return self::$cm;
    }
}

$mockConnections = new Connections(include(__DIR__ . '/connections.php'));