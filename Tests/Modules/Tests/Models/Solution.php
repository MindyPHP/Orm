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
 * @date 15/09/14.09.2014 15:11
 */

namespace Modules\Tests\Models;

use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\DateTimeField;
use Mindy\Orm\Fields\FileField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Fields\TextField;
use Mindy\Orm\Model;

class Solution extends Model
{
    const STATUS_COMPLETE = 1;
    const STATUS_SUCCESS = 2;

    public static function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className(),
            ],
            'court' => [
                'class' => CharField::className(),
            ],
            'question' => [
                'class' => CharField::className(),
            ],
            'result' => [
                'class' => CharField::className(),
            ],
            'document' => [
                'class' => FileField::className(),
                'null' => true
            ],
            'content' => [
                'class' => TextField::className(),
            ],
            'status' => [
                'class' => IntField::className(),
                'choices' => [
                    self::STATUS_SUCCESS => 'Successful',
                    self::STATUS_COMPLETE => 'Complete'
                ]
            ],
            'created_at' => [
                'class' => DateTimeField::className(),
                'autoNowAdd' => true
            ]
        ];
    }

    public function getIsComplete()
    {
        return self::STATUS_SUCCESS == $this->status;
    }
}
