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

    public function setValue($value)
    {
        if ($this->reversed) {
            $model = $this->getModel();
            $modelClass = $this->modelClass;

            if ($value) {
                $count = $modelClass::objects()->filter([
                    $this->reversedTo() => $model->pk
                ])->exclude([
                    $this->reversedTo() => $value->pk
                ])->count();

                if ($count > 0) {
                    throw new Exception('$modelClass must have unique key');
                };
                $value->pk = $model->pk;
                $value->save();
            } else {
                $modelClass::objects()->filter([
                    $this->reversedTo() => $model->pk
                ])->delete();
            }
        } else {
            $pk = $value;
            if ($value instanceof Model) {
                $pk = $value->pk;
            }

            $model = $this->getModel();
            $modelClass = $model->className();
            $name = $this->getName() . '_id';

            // TODO refact
            $avail = 0;
            if (!$model->getIsNewRecord() && $this->value && $this->value->pk == $pk) {
                $avail = 1;
            }
            // TODO refact

            if ($modelClass::objects()->filter([
                    $name => $pk
                ])->count() > $avail) {
                throw new Exception('$modelClass must have unique key');
            };
        }
        return parent::setValue($value);
    }

    public function getValue()
    {
        if ($this->reversed) {
            $modelClass = $this->modelClass;
            return $modelClass::objects()->get([
                $this->reversedTo() => $this->getModel()->pk
            ]);
        } else {
            return parent::getValue();
        }
    }

    public function reversedTo()
    {
        if (!$this->to) {
            $model = $this->getModel();
            return $model->normalizeTableName($model->classNameShort()) . '_' . $model->getPkName();
        }
        return $this->to;
    }
}
