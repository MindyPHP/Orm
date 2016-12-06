<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 14/11/16
 * Time: 10:07
 */

namespace Mindy\Bundle\MindyBundle\Admin;

class TextHelper
{
    /**
     * @param $name
     * @return string
     */
    public static function normalizeName($name) : string
    {
        return trim(strtolower(preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)), '_ ');
    }

    public static function shortName($className)
    {
        return (new \ReflectionClass($className))->getShortName();
    }
}