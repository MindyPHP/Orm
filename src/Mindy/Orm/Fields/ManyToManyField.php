<?php

namespace Mindy\Orm\Fields;

use Exception;
use Mindy\Orm\ManyToManyManager;
use Mindy\Orm\MetaData;
use Mindy\Orm\Model;
use Mindy\Orm\QuerySet;

/**
 * Class ManyToManyField
 * @package Mindy\Orm
 */
class ManyToManyField extends RelatedField
{
    public $null = true;

    /**
     * If to self, changes 'to' and 'from' fields
     * @var bool
     */
    public $reversed = false;
    /**
     * @var array
     */
    public $extra = [];
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
            $cls = $this->modelClass;
            $end = $this->getRelatedModelPk();
            if ($cls == $this->ownerClassName) {
                $end = $this->reversed ? 'from_id' : 'to_id';
            }
            $tmp = explode('\\', $cls);
            $column = $cls::normalizeTableName(end($tmp));
            $this->_relatedModelColumn = $column . '_' . $end;
        }
        return $this->_relatedModelColumn;
    }

    /**
     * @return string PK name of model
     */
    public function getModelPk()
    {
        if (!$this->_modelPk) {
            $this->_modelPk = MetaData::getInstance($this->ownerClassName)->getPkName($this->ownerClassName);
        }
        return $this->_modelPk;
    }

    /**
     * @return string Model column in "link" table
     */
    public function getModelColumn()
    {
        if (!$this->_modelColumn) {
            $cls = $this->ownerClassName;
            $end = $this->getModelPk();
            if ($cls == $this->modelClass) {
                $end = $this->reversed ? 'to_id' : 'from_id';
            }
            $tmp = explode('\\', $cls);
            $column = $cls::normalizeTableName(end($tmp));
            $this->_modelColumn = $column . '_' . $end;
        }
        return $this->_modelColumn;
    }

    /**
     * @return \Mindy\Orm\ManyToManyManager QuerySet of related objects
     */
    public function getManager()
    {
        return new ManyToManyManager($this->getRelatedModel(), [
            'modelColumn' => $this->getRelatedModelColumn(),
            'primaryModelColumn' => $this->getModelColumn(),
            'primaryModel' => $this->getModel(),
            'relatedTable' => $this->getTableName(),
            'extra' => $this->extra,
            'through' => $this->through
        ]);
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

            $fields = MetaData::getInstance($this->ownerClassName)->getFieldsInit($this->ownerClassName);
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

    protected function preformatValue($value)
    {
        if (!is_array($value)) {
            if (is_string($value) && strpos($value, ',') !== false) {
                $value = explode(',', $value);
            } else {
                $value = [$value];
            }
        }

        if (is_array($value) && count($value) > 0) {
            if (
                is_numeric($value[0]) ||
                $value[0] instanceof Model
            ) {
                return $value;
            } else {
                throw new Exception("ManyToMany field can set only arrays of Models or existing primary keys");
            }
        } else {
            return [];
        }
    }

    /**
     * @param array $value
     * @throws \Exception
     */
    public function setValue($value)
    {
        $value = $this->preformatValue($value);
        $class = $this->modelClass;
        $manager = $this->getManager();
        if (!$this->through) {
            $manager->clean();
        }
        foreach ($value as $linkModel) {
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

        return [$relatedModel, [[
            'table' => $this->getTableName(false),
            // @TODO: chained with Sync - 40 line
            'from' => $this->getModel()->getPkName(),
            'to' => $this->getModelColumn(),
            'group' => true
        ], [
            'table' => $this->getRelatedTable(false),
            // @TODO: chained with Sync - 40 line
            'from' => $this->getRelatedModelColumn(),
            'to' => $relatedModel->getPkName()
        ]]];
    }

    public function fetch($value)
    {
        // TODO: Implement fetch() method.
    }

    /**
     * @param $form
     * @param null $fieldClass
     * @param array $extra
     * @return \Mindy\Form\Fields\DropDownField
     */
    public function getFormField($form, $fieldClass = null, array $extra = [])
    {
        return parent::getFormField($form, \Mindy\Form\Fields\DropDownField::className(), $extra);
    }

    public function processQuerySet(QuerySet $qs, $alias, $autoGroup = true)
    {
        $grouped = false;
        list($relatedModel, $joinTables) = $this->getJoin();
        $throughAlias = null;
        foreach ($joinTables as $join) {
            $type = isset($join['type']) ? $join['type'] : 'LEFT OUTER JOIN';
            $newAlias = $qs->makeAliasKey($join['table']);
            $table = $join['table'] . ' ' . $newAlias;

            $from = $alias . '.' . $join['from'];
            $to = $newAlias . '.' . $join['to'];
            $on = $qs->quoteColumnName($from) . ' = ' . $qs->quoteColumnName($to);

            $qs->join($type, $table, $on);

            // Has many relations (we must work only with current model lines - exclude duplicates)
            if ($grouped === false) {
                if ($autoGroup) {
                    $qs->group([$this->getModel()->getPkName()]);
                }
                $grouped = true;
            }

            $alias = $newAlias;
            if (!$throughAlias) {
                $throughAlias = $alias;
            }
        }

        $through = null;
        if ($this->through) {
            $through = [
                new $this->through,
                $throughAlias
            ];
        }
        return [$through, [$relatedModel, $alias]];
    }
}
