<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests\Models;

use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\DateTimeField;
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
                'class' => CharField::class,
            ],
            'court' => [
                'class' => CharField::class,
            ],
            'question' => [
                'class' => CharField::class,
            ],
            'result' => [
                'class' => CharField::class,
            ],
            'document' => [
                'class' => CharField::class,
                'null' => true,
            ],
            'content' => [
                'class' => TextField::class,
            ],
            'status' => [
                'class' => IntField::class,
                'choices' => [
                    self::STATUS_SUCCESS => 'Successful',
                    self::STATUS_COMPLETE => 'Complete',
                ],
            ],
            'created_at' => [
                'class' => DateTimeField::class,
                'autoNowAdd' => true,
            ],
        ];
    }

    public function getIsComplete()
    {
        return self::STATUS_SUCCESS == $this->status;
    }
}
