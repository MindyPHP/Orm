<?php

namespace Mindy\Orm\Fields;

use Exception;
use Mindy\Orm\Model;

/**
 * Class OneToOneField
 * @package Mindy\Orm
 */
class OneToOneField extends ForeignField
{
    /**
     * Virtual or real field
     */
    public $reversed = false;

    public $to;

    public function init()
    {
        parent::init();

        if ($this->reversed) {
            $this->null = true;
        } else {
            $this->primary = true;
            $this->unique = true;
        }
    }

    public function sqlType()
    {
        return 'integer(' . (int)$this->length . ')';
    }

    public function setValue($value)
    {
        if ($value instanceof Model) {
            $value = $value->pk;
        }

        if ($this->reversed) {
            $model = $this->getRelatedModel();
            $reversed = $this->getReversedTo();

            if ($value) {
                $count = $model->objects()
                    ->filter([$reversed => $model->pk])
                    ->exclude([$reversed => $value])
                    ->count();

                if ($count > 0) {
                    throw new Exception('$modelClass must have unique key');
                }
                $value->pk = $model->pk;
                $value->save();
            } else if ($model->getIsNewRecord() === false && empty($model->pk) === false) {
                $model->objects()->filter(['pk' => $model->pk])->delete();
            }
        }
        return parent::setValue($value);
    }

    public function getValue()
    {
        if ($this->reversed) {
            return $this->getRelatedModel()->objects()->get([
                $this->getReversedTo() => $this->getModel()->pk
            ]);
        } else {
            return parent::getValue();
        }
    }

    public function getReversedTo()
    {
        if (empty($this->to)) {
            $model = $this->getRelatedModel();
            return $model->normalizeTableName($model->classNameShort()) . '_' . $model->getPkName();
        }
        return $this->to;
    }
}
