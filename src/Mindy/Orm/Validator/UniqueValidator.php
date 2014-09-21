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
 * @date 10/05/14.05.2014 15:50
 */

namespace Mindy\Orm\Validator;

use Modules\Core\CoreModule;

class UniqueValidator extends Validator
{
    public function validate($value)
    {
        $modelClass = $this->getModel();
        $qs = $modelClass::objects()->filter([$this->getName() => $value]);
        if($qs->count() > 0) {
            $this->addError(CoreModule::t("Value must be a unique"));
        }

        return $this->hasErrors() === false;
    }
}
