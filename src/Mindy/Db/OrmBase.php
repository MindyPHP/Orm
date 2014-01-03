<?php

/**
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 03/01/14.01.2014 22:10
 */

namespace Mindy\Db;


class OrmBase
{
    /**
     * @var \Mindy\Db\Connection
     */
    private static $_connection;

    /**
     * @param Connection $connection
     */
    public static function setConnection(Connection $connection)
    {
        self::$_connection = $connection;
    }

    /**
     * @return \Mindy\Db\Connection
     */
    public function getConnection()
    {
        return self::$_connection;
    }

    /**
     * Return table name based on this class name.
     * Override this method for custom table name.
     * @return string
     */
    public static function tableName()
    {
        $className = get_called_class();
        $normalizeClass = rtrim(str_replace('\\', '/', $className), '/\\');
        if (($pos = mb_strrpos($normalizeClass, '/')) !== false) {
            $class = mb_substr($normalizeClass, $pos + 1);
        } else {
            $class = $normalizeClass;
        }
        return trim(strtolower(preg_replace('/(?<![A-Z])[A-Z]/', '_\0', $class)), '_');
    }
}
