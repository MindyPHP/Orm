<?php

namespace Mindy\Orm;

use Mindy\Base\Mindy;

/**
 * Class Model
 * @package Mindy\Orm
 */
class Model extends Orm
{
    public function getVerboseName()
    {
        return $this->classNameShort();
    }

    /**
     * @return \Mindy\Base\Module
     */
    public static function getModule()
    {
        return Mindy::app()->getModule(self::getModuleName());
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
        return Mindy::app()->urlManager->reverse($route, $data);
    }

    public static function t($str, $params = [], $dic = 'main')
    {
        return self::getModule()->t($str, $params, $dic);
    }
}
