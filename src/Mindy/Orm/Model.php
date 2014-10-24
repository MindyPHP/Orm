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
 * @date 03/01/14.01.2014 21:52
 */

namespace Mindy\Orm;

use Mindy\Base\Mindy;

class Model extends Orm
{
    public function getVerboseName()
    {
        return $this->classNameShort();
    }

    /**
     * @return \Mindy\Base\Module
     */
    public function getModule()
    {
        return Mindy::app()->getModule(self::getModuleName());
    }

    public function getAdminNames($instance = null)
    {
        $module = $this->getModule();
        $cls = self::classNameShort();
        if ($instance) {
            $updateTranslate = $module->t('Update ' . strtolower($cls) . ': {name}', ['{name}' => (string)$instance]);
        } else {
            $updateTranslate = $module->t('Update ' . strtolower($cls));
        }
        return [
            $module->t($cls . 's'),
            $module->t('Create ' . strtolower($cls)),
            $updateTranslate,
        ];
    }

    public function reverse($route, $data = null)
    {
        return Mindy::app()->urlManager->reverse($route, $data);
    }
}
