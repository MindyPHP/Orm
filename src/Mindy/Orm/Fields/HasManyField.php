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
    protected $_model;

    public $from;
    public $to;

    public $modelClass;

    public function init()
    {
        $this->_relatedModel = new $this->modelClass([
            'autoInitFields' => false
        ]);

        $hasPrimaryKey = false;
        foreach($this->_relatedModel->getFields() as $name => $config) {
            if(is_subclass_of('\Mindy\Orm\Field\AutoField', $config['class'])) {
                $hasPrimaryKey = true;
                $this->from = $name;
                break;
            }
        }

        if(!$hasPrimaryKey) {
            $this->from = 'id';
        }
    }

    public function setModel(Model $model)
    {
        $this->_model = $model;

        if (!$this->to) {
            // $this->foreignKey = $this->_model->tableName() . '_' . $this->_model->getPkName();
            $this->to = $this->_model->getPkName();
        }
    }

    public function sqlType()
    {
        return false;
    }

    public function getQuerySet()
    {
        $qs = new QuerySet([
            'model' => $this->_relatedModel,
            'modelClass' => $this->modelClass
        ]);

        return $qs->filter([
            $this->to => $this->_model->{$this->from}
        ]);
    }
}
