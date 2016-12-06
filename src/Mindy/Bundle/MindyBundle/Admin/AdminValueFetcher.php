<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 14/11/2016
 * Time: 00:08
 */

namespace Mindy\Bundle\MindyBundle\Admin;

use Mindy\Orm\Fields\BooleanField;
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Fields\ManyToManyField;
use Mindy\Orm\ModelInterface;

class AdminValueFetcher
{

    /**
     * @param $column
     * @param $model
     * @return array
     */
    public function getChainedModel($column, $model)
    {
        if (strpos($column, '__') !== false) {
            $exploded = explode('__', $column);
            $last = count($exploded) - 1;
            $column = null;
            foreach ($exploded as $key => $name) {
                if ($model instanceof ModelInterface) {
                    $value = $model->{$name};
                    $column = $name;
                    if ($key != $last && $value) {
                        $model = $value;
                    }
                } else {
                    $model = null;
                    break;
                }
            }
        }
        return [$column, $model];
    }

    /**
     * @param $column
     * @param ModelInterface|\Mindy\Orm\Model $model
     * @return mixed
     */
    public function fetchValue($column, ModelInterface $model)
    {
        list($column, $model) = $this->getChainedModel($column, $model);
        if ($model === null) {
            return null;
        }

        $column = $model->convertToPrimaryKeyName($column);
        $booleanHtml = '<i class="icon checkmark" aria-hidden="true"></i>';
        if ($model->hasField($column)) {
            $field = $model->getField($column);
            if ($field instanceof ManyToManyField || $field instanceof HasManyField) {
                return get_class($model->{$column});
            } else {
                $value = $model->{$column};

                if ($model->getField($column) instanceof BooleanField) {
                    return $value ? $booleanHtml : '';
                } else {
                    return $value;
                }
            }
        } else {
            $method = 'get' . ucfirst($column);
            if (method_exists($model, $method)) {
                return $model->{$method}();
            }
        }
        return null;
    }
}