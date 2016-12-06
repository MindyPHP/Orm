<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 07/10/16
 * Time: 15:37
 */

namespace Mindy\Bundle\MindyBundle\Model;

use Mindy\Orm\Fields\CharField;
use Mindy\Orm\Fields\TextField;
use Mindy\Orm\Model;
use function Mindy\trans;
use Symfony\Component\Validator\Constraints as Assert;

class Meta extends Model
{
    public static function getFields()
    {
        return [
            'domain' => [
                'class' => CharField::class,
                'verboseName' => trans('meta.model.domain')
            ],
            'title' => [
                'class' => CharField::class,
                'verboseName' => trans('meta.model.title')
            ],
            'url' => [
                'class' => CharField::class,
                'verboseName' => trans('meta.model.url')
            ],
            'keywords' => [
                'class' => CharField::class,
                'verboseName' => trans('meta.model.keywords')
            ],
            'canonical' => [
                'class' => CharField::class,
                'verboseName' => trans('meta.model.canonical'),
                'validators' => [
                    new Assert\Url()
                ]
            ],
            'description' => [
                'class' => TextField::class,
                'verboseName' => trans('meta.model.description')
            ]
        ];
    }
}