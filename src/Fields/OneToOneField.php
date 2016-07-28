<?php

namespace Mindy\Orm\Fields;

use Exception;
use Mindy\Orm\Model;
use Mindy\QueryBuilder\Expression;
use Mindy\Validation\UniqueValidator;

/**
 * Class OneToOneField
 * @package Mindy\Orm
 */
class OneToOneField extends ForeignField
{
    /**
     * @var bool virtual or real field
     */
    public $reversed = false;
    /**
     * @var
     */
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

    public function reversedTo()
    {
        if (!$this->to) {
            $model = $this->getModel();
            return $model->normalizeTableName($model->classNameShort()) . '_' . $model->getPkName();
        }
        return $this->to;
    }

    public function getDbPrepValue()
    {
        if ($this->primary && $this->getModel()->getDb()->driverName == 'pgsql') {
            // Primary key всегда передается по логике Query, а для корректной работы pk в pgsql
            // необходимо передать curval($seq) или nextval($seq) или не экранированный DEFAULT.
            //
//            $sequenceName = $db->getSchema()->getTableSchema($this->getModel()->tableName())->sequenceName;
//            return new Expression("nextval('" . $sequenceName . "')");
            return new Expression("DEFAULT");
        } else {
            return parent::getDbPrepValue();
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
                    $this->reversedTo() => $value
                ])->count();
                if ($count > 0) {
                    throw new Exception(get_class($this->getRelatedModel()) . ' must have unique key');
                }
                $value->pk = $model->pk;
                $value->save();
            } else {
                $modelClass::objects()->filter([
                    $this->reversedTo() => $model->pk
                ])->delete();
            }
        } else {
            if ($value) {
                $currentValue = $this->getRelatedModel()->{$this->to};
                if ($currentValue) {
                    $relatedCount = $this->getRelatedModel()->objects()->filter([$this->to => $currentValue])->count();
                } else {
                    $relatedCount = 0;
                }
                $count = $this->getModel()->objects()->filter([$this->getName() . '_id' => $value])->count();
                if ($relatedCount > 0 && $count > 0) {
                    throw new Exception(get_class($this->getModel()) . ' failed to assign value');
                }
            }
        }
        return parent::setValue($value);
    }

    public function getValue()
    {
        if ($this->reversed) {
            return $this->getRelatedModel()->objects()->get([
                $this->to => $this->getModel()->pk
            ]);
        } else {
            return parent::getValue();
        }
    }
}
