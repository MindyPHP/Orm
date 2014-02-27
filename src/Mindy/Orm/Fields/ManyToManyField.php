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


use Mindy\Orm\Model;
use Mindy\Orm\Relation;

class ManyToManyField extends RelatedField
{
    /**
     * @var null|string
     */
    public $through;

    /**
     * @var string
     */
    public $modelClass;

    /**
     * @var string
     */
    public $via;

    /**
     * @var array
     */
    public $viaTable;

    /**
     * @var array
     */
    public $params = [];

    /**
     * @var \Mindy\Orm\Model
     */
    private $_model;

    /**
     * @var string
     */
    private $_tableName;

    /**
     * @var
     */
    private $_columns = [];

    /**
     * @param \Mindy\Orm\Model $modelClass
     */
    public function __construct($modelClass)
    {
        // TODO ugly, refactoring
        $this->modelClass = $modelClass;

        $pk = $modelClass::primaryKey();

        $column = $modelClass::tableName() . '_id';
        $this->addColumn($column);

        $link = [$pk => $column];
        $this->params = [
            'modelClass' => $modelClass,
            'link' => $link,
            'multiple' => true,
        ];
    }

    public function sqlType()
    {
        return false;
    }

    public function setModel(Model $model)
    {
        $this->_model = $model;

        // TODO ugly, refactoring
        if ($this->through) {
            $this->via = $this->through;
        } else {
            $pk = $model->getPkName();

            $this->setTableName($model);
            $column = $model->tableName() . '_id';
            $this->addColumn($column);
            $this->viaTable = [
                $this->getTableName(), [$column => $pk]
            ];
        }
    }

    public function getModel()
    {
        return $this->_model;
    }

    public function getRelation()
    {
        $relation = new Relation($this->params);
        $relation->primaryModel = $this->getModel();
        if ($this->via) {
            $relation->via($this->via);
        } else {
            list($tableName, $link) = $this->viaTable;
            $relation->viaTable($tableName, $link);
        }
        return $relation;
    }

    public function setTableName(Model $model)
    {
        $modelClass = $this->modelClass;
        $this->_tableName = $model->tableName() . '_' . $modelClass::tableName();
    }

    public function getTableName()
    {
        return $this->_tableName;
    }

    public function addColumn($column)
    {
        $this->_columns[$column] = 'int';
    }

    public function getColumns()
    {
        return $this->_columns;
    }

    public function getOptions()
    {
        // TODO
        return [
            'through' => $this->through
        ];
    }
}
