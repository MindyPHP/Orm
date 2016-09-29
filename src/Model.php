<?php

namespace Mindy\Orm;

use function Mindy\app;
use Mindy\Form\FormModelInterface;
use Mindy\Helper\Alias;
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
        return sprintf("%s_%s", self::normalizeTableName(self::getModuleName()), parent::tableName());
    }

    /**
     * todo refact
     * Return module name
     * @return string
     */
    public static function getModuleName()
    {
        /** @var array $raw */
        // See issue #105
        // https://github.com/studio107/Mindy_Orm/issues/105
        // $raw = explode('\\', get_called_class());
        // return $raw[1];

        $object = new ReflectionClass(get_called_class());
        $modulesPath = Alias::get('Modules');
        $tmp = explode(DIRECTORY_SEPARATOR, str_replace($modulesPath, '', dirname($object->getFilename())));
        $clean = array_filter($tmp);
        return array_shift($clean);
    }

    /**
     * @return \Mindy\Base\ModuleInterface
     */
    public static function getModule()
    {
        if (($name = self::getModuleName()) && app()->hasModule($name)) {
            return app()->getModule(self::getModuleName());
        }

        return null;
    }

    public function reverse($route, $data = null)
    {
        return app()->urlManager->reverse($route, $data);
    }

    public static function t($id, array $parameters = [], $locale = null)
    {
        return trans(sprintf('modules.%s.main', self::getModuleName()), $id, $parameters, $locale);
    }

    public function __toString()
    {
        return (string)$this->classNameShort();
    }
}
