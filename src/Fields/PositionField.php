<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 28/09/16
 * Time: 22:56
 */

namespace Mindy\Orm\Fields;

use Mindy\Orm\ModelInterface;
use Mindy\Orm\TreeModel;

/**
 * Class PositionField
 * @package Modules\Core\Fields\Orm
 */
class PositionField extends IntField
{
    /**
     * @var \Closure
     */
    public $callback;

    /**
     * @param ModelInterface $model
     * @param $value
     */
    public function beforeInsert(ModelInterface $model, $value)
    {
        if (is_null($value) || $value === '') {
            $model->setAttribute($this->getName(), $this->getNextPosition($model));
        }
    }

    /**
     * @param ModelInterface $model
     * @return int
     */
    public function getNextPosition(ModelInterface $model) : int
    {
        if ($this->callback instanceof \Closure) {
            $qs = $this->callback->__invoke($model);
        } else {
            $qs = $model->objects();
            if ($model instanceof TreeModel && !empty($model->parent_id)) {
                $qs->filter(['parent_id' => $model->parent_id]);
            }
        }

        $max = (int)$qs->max($this->getName());
        return $max ? $max + 1 : 1;
    }
}
