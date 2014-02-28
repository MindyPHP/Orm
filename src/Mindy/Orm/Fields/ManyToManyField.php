<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 03/01/14.01.2014 22:03
 */

namespace Mindy\Orm\Fields;

use Mindy\Helper\Creator;
use Mindy\Orm\Model;
use Mindy\Orm\RelatedQuerySet;
use Mindy\Orm\Relation;

class ManyToManyField extends RelatedField
{
    /**
     * @var null|string
     */
    public $through;

    /**
     * Related model class
     * @var string
     */
    public $modelClass;

    /**
     * Main model
     * @var \Mindy\Orm\Model
     */
    private $_model;

    /**
     * Related model
     * @var \Mindy\Orm\Model
     */
    private $_relatedModel;

    private $_modelPk;
    private $_relatedModelPk;

    private $_modelColumn;
    private $_relatedModelColumn;

    /**
     * @var array
     */
    public $params = [];

    /**
     * @var string
     */
    private $_tableName;

    /**
     * @var
     */
    private $_columns = [];

    /**
     * @param Model $modelClass
     * @param array $options
     */
    public function __construct($modelClass, array $config=[])
    {
        // TODO ugly, refactoring
        if (!empty($config)) {
            Creator::configure($this, $config);
        }
        $this->modelClass = $modelClass;
        $this->_relatedModel = new $this->modelClass();
        $this->_relatedModelPk = $this->_relatedModel->getPkName();
        $this->_relatedModelColumn =$this->_relatedModel->tableName() . '_' . $this->_relatedModelPk;

        if (!$this->through){
            $fields = $this->_relatedModel->getFieldsInit();
            $this->addColumn($this->_relatedModelColumn, $fields[$this->_relatedModelPk]->sqlType());
        }
    }

    public function sqlType()
    {
        return false;
    }

    public function setModel(Model $model)
    {
        $this->_model = $model;

        // TODO ugly, refactoring

        $this->_modelPk = $model->getPkName();
        $this->_modelColumn = $model->tableName() . '_' . $this->_modelPk;
        $fields = $model->getFieldsInit();

        if ($this->through) {
            $through = $this->through;
            $this->_tableName = $through::tableName();
        }else{
            $this->setTableName($model);
            $this->addColumn($this->_modelColumn, $fields[$this->_modelPk]->sqlType());
        }
    }

    public function getModel()
    {
        return $this->_model;
    }

    public function getQuerySet()
    {
        $qs = new RelatedQuerySet([
            'model' => $this->_relatedModel,
            'modelClass' => $this->modelClass,
            'modelColumn' => $this->_relatedModelColumn,

            'primaryModel' => $this->_model,
            'primaryModelColumn' => $this->_modelColumn,

            'relatedTable' => $this->getTableName()
        ]);

        $qs->join('INNER JOIN',
            $this->getTableName(),
            [$this->getTableName() . '.' . $this->_modelColumn => $this->_model->getPk()]
        );

        return $qs;
    }

    public function setTableName(Model $model)
    {
        $tableName = [$model->tableName(), $this->_relatedModel->tableName()];
        sort($tableName);
        $this->_tableName = implode('_', $tableName);
    }

    public function getTableName()
    {
        return $this->_tableName;
    }

    public function addColumn($column, $type=null)
    {
        $this->_columns[$column] = (empty($type) || $type == 'pk') ? 'int' : $type;
    }

    public function getColumns()
    {
        return $this->_columns;
    }

    protected function setOptions(array $options){
        // TODO: safe check / remove on stable
        foreach($options as $name => $value){
            $this->$name = $value;
        }
    }
}
