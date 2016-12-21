<?php

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\BlobField;
use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\IntField;
use Mindy\Orm\Model;

/**
 * Class Session.
 */
class Session extends Model
{
    public static function getFields()
    {
        return [
            'id' => [
                'class' => CharField::class,
                'length' => 32,
                'primary' => true,
                'null' => false,
            ],
            'expire' => [
                'class' => IntField::class,
                'null' => false,
            ],
            'data' => [
                'class' => BlobField::class,
                'null' => true,
            ],
        ];
    }

    public function __toString()
    {
        return (string) $this->id;
    }
}
