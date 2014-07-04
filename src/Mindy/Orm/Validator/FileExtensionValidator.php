<?php
/**
 * Created by PhpStorm.
 * User: antonokulov
 * Date: 04/07/14
 * Time: 11:33
 */

namespace Mindy\Orm\Validator;

use Mindy\Base\Mindy;

class FileExtensionValidator extends Validator
{
    public $allowedTypes = null;

    public function __construct($allowedTypes = null)
    {
        $this->allowedTypes = $allowedTypes;
    }

    public function validate($value)
    {
        $filename = '';
        if (is_array($value)){
            if (isset($value['name']))
                $filename = $value['name'];
        }elseif(is_string($value)){
            $filename = $value;
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if ($ext && is_array($this->allowedTypes)){
            if (!in_array($ext, $this->allowedTypes)){
                $this->addError( implode(' ',[
                    Mindy::app()->t("Is not a valid file type:"),
                    $ext . '.',
                    Mindy::app()->t("Types allowed:"),
                    implode(', ', $this->allowedTypes) . '.'
                ]));
            }

        }
        return $this->hasErrors() === false;
    }
}