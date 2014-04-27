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

use Exception;
use Mindy\Orm\ManyToManyManager;
use Mindy\Orm\Model;

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
    protected $_model;

    /**
     * Related model
     * @var \Mindy\Orm\Model
     */
    protected $_relatedModel;

    /**
     * Primary key name
     * @var string
     */
    protected $_modelPk;

    /**
     * Primary key name of the related model
     * @var string
     */
    protected $_relatedModelPk;

    /**
     * Model column in "link" table
     * @var string
     */
    protected $_modelColumn;

    /**
     * Related model column in "link" table
     * @var string
     */
    protected $_relatedModelColumn;

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
    protected $_columns = [];

    /**
     * Initialization
     */
    public function init()
    {

    }

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

    /**
     * @return string PK name of related model
     */
    public function getRelatedModelPk()
    {
        if (!$this->_relatedModelPk) {
            $this->_relatedModelPk = $this->getRelatedModel()->getPkName();
        }
        return $this->_relatedModelPk;
    }

    /**
     * @return string Related model column in "link" table
     */
    public function getRelatedModelColumn()
    {
        if (!$this->_relatedModelColumn) {
            $this->_relatedModelColumn = $this->getRelatedTable() . '_' . $this->getRelatedModelPk();
        }
        return $this->_relatedModelColumn;
    }

    /**
     * @return string PK name of model
     */
    public function getModelPk()
    {
        if (!$this->_modelPk) {
            $this->_modelPk = $this->getModel()->getPkName();
        }
        return $this->_modelPk;
    }

    /**
     * @return string Model column in "link" table
     */
    public function getModelColumn()
    {
        if (!$this->_modelColumn) {
            $this->_modelColumn = $this->getTable() . '_' . $this->getModelPk();
        }
        return $this->_modelColumn;
    }


    /**
     * @return \Mindy\Orm\ManyToManyManager QuerySet of related objects
     */
    public function getManager()
    {
        $manager = new ManyToManyManager($this->getRelatedModel(), [
            'modelColumn' => $this->getRelatedModelColumn(),
            'primaryModelColumn' => $this->getModelColumn(),

            'primaryModel' => $this->getModel(),
            'relatedTable' => $this->getTableName()
        ]);

        return $manager;
    }

    /**
     * Table name of the "link" table
     * @return string
     */
    public function getTableName()
    {
        if (!$this->_tableName) {
            if (!$this->through) {
                $parts = [$this->getTable(), $this->getRelatedTable()];
                sort($parts);
                $this->_tableName = '{{%' . implode('_', $parts) . '}}';
            } else {
                $through = $this->through;
                $this->_tableName = $through::tableName();
            }
        }
        return $this->_tableName;
    }

    /**
     * @param string $column Column name in "link" table
     * @param null|string $type Type of the column ('int' by default)
     */
    public function addColumn($column, $type = null)
    {
        $this->_columns[$column] = (empty($type) || $type == 'pk') ? 'int' : $type;
    }

    /**
     * @return array "link" table columns
     */
    public function getColumns()
    {
        if (!$this->through) {
            $fields = $this->getRelatedModel()->getFieldsInit();
            $this->addColumn($this->getRelatedModelColumn(), $fields[$this->getRelatedModelPk()]->sqlType());

            $fields = $this->getModel()->getFieldsInit();
            $this->addColumn($this->getModelColumn(), $fields[$this->getModelPk()]->sqlType());
        }
        return $this->_columns;
    }

    /**
     * @return bool|string
     */
    public function sqlType()
    {
        return false;
    }

    /**
     * @param array $value
     * @throws \Exception
     */
    public function setValue($value)
    {
        if (!is_array($value)) {
            throw new Exception("ManyToMany field can set only arrays of Models or existing primary keys");
        }
        $class = $this->modelClass;
        $manager = $this->getManager();
        $manager->clean();
        foreach ($value as $model) {
            $linkModel = $model;
            if (!is_a($linkModel, $this->modelClass)) {
                $linkModel = $class::objects()->get(['pk' => $linkModel]);
            }
            if (is_a($linkModel, $this->modelClass)) {
                $manager->link($linkModel);
            } else {
                throw new Exception("ManyToMany field can set only arrays of Models or existing primary keys");
            }
        }
    }

    public function getJoin()
    {
        $relatedModel = $this->getRelatedModel();

        return array($relatedModel, array(
            array(
                'table' => $this->getTableName(false),
                // @TODO: chained with Sync - 40 line
                'from' => $this->getModel()->getPkName(),
                'to' => $this->getModelColumn(),
                'group' => true
            ),
            array(
                'table' => $this->getRelatedTable(false),
                // @TODO: chained with Sync - 40 line
                'from' => $this->getRelatedModelColumn(),
                'to' => $relatedModel->getPkName()
            )
        ));
    }
}
