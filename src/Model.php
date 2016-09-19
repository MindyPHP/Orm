<?php

namespace Mindy\Orm;

use function Mindy\app;
use Mindy\Helper\Alias;
use ReflectionClass;

/**
 * Class Model
 * @package Mindy\Orm
 */
class Model extends NewOrm
{
    public function getVerboseName()
    {
        return $this->classNameShort();
    }

    /**
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

    public function getAdminNames($instance = null)
    {
        $module = $this->getModule();
        $cls = self::classNameShort();
        $name = self::normalizeName($cls);
        if ($instance) {
            $updateTranslate = $module->t('Update ' . $name . ': {name}', ['{name}' => (string)$instance]);
        } else {
            $updateTranslate = $module->t('Update ' . $name);
        }
        return [
            $module->t(ucfirst($name . 's')),
            $module->t('Create ' . $name),
            $updateTranslate,
        ];
    }

    public static function normalizeName($name)
    {
        return trim(strtolower(preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)), '_ ');
    }

    public function reverse($route, $data = null)
    {
        return app()->urlManager->reverse($route, $data);
    }

    public static function t($str, $params = [], $dic = 'main')
    {
        return self::getModule()->t($str, $params, $dic);
    }
}
