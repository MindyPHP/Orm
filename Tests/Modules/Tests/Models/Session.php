<?php

namespace Modules\Tests\Models;

use Mindy\Orm\Fields\BlobField;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Model;

/**
 * Class Session
 * @package Modules\User
 */
class Session extends Model
{
    public static function getFields()
    {
        return [
            'id' => [
                'class' => CharField::className(),
                'length' => 32,
                'primary' => true,
                'null' => false,
            ],
            'expire' => [
                'class' => IntField::className(),
                'null' => false,
            ],
            'data' => [
                'class' => BlobField::className(),
                'null' => true,
            ]
        ];
    }

    public function __toString()
    {
        return (string)$this->id;
    }
}
