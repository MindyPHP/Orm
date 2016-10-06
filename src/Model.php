<?php

namespace Mindy\Orm;

use function Mindy\app;
use Mindy\Base\Application;
use Mindy\Form\FormModelInterface;
use function Mindy\trans;
use ReflectionClass;

/**
 * Class Model
 * @package Mindy\Orm
 */
class Model extends NewOrm implements FormModelInterface
{
    /**
     * @return string
     */
    public function getVerboseName() : string
    {
        return $this->classNameShort();
    }

    /**
     * @return string
     */
    public function classNameShort() : string
    {
        $classMap = explode('\\', get_called_class());
        return end($classMap);
    }

    /**
     * @return string
     */
    public static function tableName() : string
    {
        if (defined('MINDY_ORM_TEST') && MINDY_ORM_TEST) {
            return parent::tableName();
        } else {
            $bundleName = str_replace('Bundle', '', self::getBundleName());
            return sprintf("%s_%s", self::normalizeTableName($bundleName), parent::tableName());
        }
    }

    /**
     * @deprecated 
     * Return module name
     * @return string
     */
    public static function getModuleName()
    {
        return self::getBundleName();
    }

    /**
     * Return module name
     * @return string
     */
    public static function getBundleName()
    {
        $object = new ReflectionClass(get_called_class());
        $shortPath = substr($object->getFileName(), strpos($object->getFileName(), 'Bundle') + 7);
        return substr($shortPath, 0, strpos($shortPath, '/'));
    }

    /**
     * @deprecated 
     */
    public static function getModule()
    {
        return self::getBundle();
    }

    /**
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle|null
     */
    public static function getBundle()
    {
        if ($app = app()) {
            return $app->getKernel()->getBundle(self::getBundleName());
        }

        return null;
    }
 
    public function reverse($route, array $data = [])
    {
        return app()->router->generate($route, $data);
    }

    public function __toString()
    {
        return (string)$this->classNameShort();
    }
}
