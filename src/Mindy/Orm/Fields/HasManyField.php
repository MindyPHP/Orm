<?php

namespace Mindy\Orm\Fields;

use Mindy\Orm\Model;
use Mindy\Orm\QuerySet;

class HasManyField extends RelatedField
{
    /**
     * @var \Mindy\Orm\Model
     */
    protected $_relatedModel;

    /**
     * @var \Mindy\Orm\Model
     */
    protected $_model;

    public $from;
    public $to;

    public $modelClass;

    public function init()
    {

    }

    /**
     * @param \Mindy\Orm\Model $model
     */
    public function setModel(Model $model)
    {
        $this->_model = $model;
    }

    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @return \Mindy\Orm\Model
     */
    public function getRelatedModel()
    {
        if (!$this->_relatedModel) {
            $this->_relatedModel = new $this->modelClass();
        }
        return $this->_relatedModel;
    }

    public function sqlType()
    {
        return false;
    }

    public function getQuerySet()
    {
        $qs = new QuerySet([
            'model' => $this->getRelatedModel(),
            'modelClass' => $this->modelClass
        ]);

        return $qs->filter([
            $this->to() => $this->getModel()->{$this->from()}
        ]);
    }

    public function to()
    {
        if (!$this->to) {
            $this->to = $this->getModel()->tableName() . '_' . $this->getModel()->getPkName();
        }
        return $this->to;
    }

    public function from()
    {
        if (!$this->from) {
            $this->from = $this->getModel()->getPkName();
        }
        return $this->from;
    }
}
