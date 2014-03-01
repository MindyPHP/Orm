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
 * @date 04/01/14.01.2014 00:44
 */

namespace Tests\Models;


use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Model;
use Mindy\Orm\Validator\MinLengthValidator;

class ValidationModel extends Model
{
    public function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className(),
                'validators' => [
                    new MinLengthValidator(6),
                    function ($value) {
                        if (mb_strlen($value, 'UTF-8') > 10) {
                            return "Maximum name field is 10";
                        }

                        return true;
                    }
                ]
            ],
        ];
    }
}
