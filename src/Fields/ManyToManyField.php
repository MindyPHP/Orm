<?php

namespace Mindy\Orm\Fields;

use Exception;
use Mindy\Orm\Manager;
use Mindy\Orm\ManyToManyManager;
use Mindy\Orm\ManyToManyManagerTrait;
use Mindy\Orm\MetaData;
use Mindy\Orm\Model;
use Mindy\Orm\Orm;
use Mindy\Orm\QuerySet;
use Mindy\QueryBuilder\QueryBuilder;

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
     * @var array
     */
    public $throughLink = [];
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
     * @var
     */
    private $_manager;

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
        if (!empty($this->throughLink)) {
            list($fromId, $toId) = $this->throughLink;
            return $toId;
        }

        if (!$this->_relatedModelColumn) {
            $cls = $this->modelClass;
            $end = $this->getRelatedModelPk();
            if ($cls == $this->ownerClassName) {
                $end = $this->reversed ? 'from_id' : 'to_id';
            }
            $tmp = explode('\\', $cls);
            $column = Orm::normalizeTableName(end($tmp));
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
     * @throws Exception
     */
    public function getModelColumn()
    {
        if (empty($this->_modelColumn)) {
            if (!empty($this->through)) {
                if (empty($this->throughLink)) {
                    $throughClass = $this->through;
                    $through = new $throughClass;

                    $name = '';
                    foreach ($through->getFields() as $fieldName => $params) {
                        if (isset($params['modelClass']) && $params['modelClass'] == $this->ownerClassName) {
                            $name = $fieldName;
                            break;
                        }
                    }

                    $this->_modelColumn = $name . '_id';
                } else {
                    list($fromId, $toId) = $this->throughLink;
                    if (empty($this->throughLink)) {
                        throw new Exception('throughLink is missing in configutaion');
                    }

                    $this->_modelColumn = $this->reversed ? $toId : $fromId;
                }
            } else {
                $cls = $this->ownerClassName;
                $end = $this->getModelPk();
                if ($cls == $this->modelClass) {
                    $end = $this->reversed ? 'to_id' : 'from_id';
                }
                $tmp = explode('\\', $cls);
                $column = Orm::normalizeTableName(end($tmp));
                $this->_modelColumn = $column . '_' . $end;
            }
        }
        return $this->_modelColumn;
    }

    protected function unsafeGetManager()
    {
        if ($this->_manager === null) {
            $className = 'ManyToManyWrapperManager_' . rand(0, 100000) . time();
            $traits = [
                ManyToManyManagerTrait::class
            ];
            $class = sprintf('class %s extends \%s { use %s; }', $className, get_class($this->getRelatedModel()->objects()), implode(', ', $traits));
            eval($class);
            $options = [
                'modelColumn' => $this->getRelatedModelColumn(),
                'primaryModelColumn' => $this->getModelColumn(),
                'primaryModel' => $this->getModel(),
                'relatedTable' => $this->getTableName(),
                'extra' => $this->extra,
                'through' => $this->through,
                'throughLink' => $this->throughLink
            ];
            $this->_manager = new $className($this->getRelatedModel(), $options);
        }
        return $this->_manager;
    }

    /**
     * @return \Mindy\Orm\ManyToManyManager QuerySet of related objects
     */
    public function getManager()
    {
        return $this->unsafeGetManager();
    }

    /**
     * Table name of the "link" table
     * @return string
     */
    public function getTableName()
    {
        if (!$this->through) {
            $adapter = $this->getRelatedModel()->getDb()->getAdapter();
            $parts = [$adapter->getRawTableName($this->getTable()), $adapter->getRawTableName($this->getRelatedTable())];
            sort($parts);
            var_dump('{{%' . implode('_', $parts) . '}}');
            return '{{%' . implode('_', $parts) . '}}';
        } else {
            $cls = $this->through;
            return $cls::tableName();
        }
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

            $fields = MetaData::getInstance($this->ownerClassName)->getFieldsInit();
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
        if (empty($value)) {
            return [];
        }

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
                if (empty($value[0])) {
                    return [];
                }

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

    protected function getThroughTableName()
    {
        if ($this->through) {
            $cls = $this->through;
            return $cls::tableName();
        } else {

        }
    }

    public function getJoin(QueryBuilder $qb, $topAlias)
    {
        $relatedModel = $this->getRelatedModel();
        $throughAlias = $qb->makeAliasKey($this->getTableName());
        $alias = $qb->makeAliasKey($this->getRelatedTable());
        return [
            [
                'LEFT JOIN',
                $this->getTableName(),
                [
                    $this->getRelatedModelColumn() => $topAlias . '.' . $relatedModel->getPkName()
                ],
                $throughAlias
            ], [
                'LEFT JOIN',
                $this->getRelatedTable(),
                [
                    $alias . '.' . $this->getModel()->getPkName() => $throughAlias . '.' . $this->getModelColumn()
                ],
                $alias
            ]
        ];
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
}
