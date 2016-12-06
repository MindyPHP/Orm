<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 22/11/2016
 * Time: 20:44
 */

namespace Mindy\Bundle\MindyBundle\Form\DataTransformer;

use Mindy\Orm\Manager;
use Mindy\Orm\QuerySet;
use Symfony\Component\Form\DataTransformerInterface;

class QuerySetTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if ($value instanceof Manager || $value instanceof QuerySet) {
            return $value->all();
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        return $value;
    }
}