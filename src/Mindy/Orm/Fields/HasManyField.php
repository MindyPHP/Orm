<?php
/**
 * Created by JetBrains PhpStorm.
 * User: new
 * Date: 2/28/14
 * Time: 6:51 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Mindy\Orm\Fields;

use Mindy\Helper\Creator;
use Mindy\Orm\Model;
use Mindy\Orm\QuerySet;

class HasManyField extends RelatedField
{

    protected $_relatedModel;
    protected $_model;

    public $from = 'pk';
    public $to;

    public $modelClass;

    public function init()
    {
        $this->_relatedModel = new $this->modelClass([
            'autoInitFields' => false
        ]);
    }

    public function setModel(Model $model)
    {
        $this->_model = $model;

        if (!$this->to) {
            $this->foreignKey = $this->_model->tableName() . '_' . $this->_model->getPkName();
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
