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
 * @date 04/01/14.01.2014 00:02
 */

namespace Tests\Models;


use Mindy\Db\Fields\CharField;
use Mindy\Db\Model;
use Mindy\Db\Validator\MinLengthValidator;

class ClassValidationModel extends Model
{
    public function getFields()
    {
        return [
            'name' => new CharField([
                    'validators' => [
                        new MinLengthValidator(6),
                    ]
                ]),
        ];
    }
}
