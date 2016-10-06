<?php

namespace Mindy\Orm;

use function Mindy\app;
use function Mindy\trans;
use Mindy\Form\FormModelInterface;
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
    public function getVerboseName(): string
    {
        return $this->classNameShort();
    }

    /**
     * @return string
     */
    public function classNameShort(): string
    {
        $classMap = explode('\\', get_called_class());
        return end($classMap);
    }

    /**
     * @return string
     */
    public static function tableName(): string
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

    public static function getBundle()
    {
        return app()->getKernel()->getBundle(self::getBundleName());
    }

    public function reverse($route, array $data = [])
    {
        return app()->router->generate($route, $data);
    }

    public static function t($id, array $parameters = [], $domain = null, $locale = null)
    {
        $translator = app()->getContainer()->get('translator');
        return $translator->trans($id, $parameters, $domain ? $domain : sprintf('%s.messages', self::getBundleName()), $locale);
    }

    public function __toString()
    {
        return (string) $this->classNameShort();
    }
}
